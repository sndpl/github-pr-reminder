<?php
namespace App\Service;

class SlackService
{
    protected $token;
    protected $botName = 'github-pr-reminder';
    protected $botIcon;

    public function __construct(string $token, string $botName, string $botIcon)
    {
        $this->token = $token;
        $this->botName = $botName;
        $this->botIcon = $botIcon;
    }

    /**
     * Send a Message to a Slack Channel.
     *
     * In order to get the API Token visit: https://api.slack.com/custom-integrations/legacy-tokens
     * The token will look something like this `xoxo-2100000415-0000000000-0000000000-ab1ab1`.
     *
     * @param string $message The message to post into a channel.
     * @param string $channel The name of the channel prefixed with #, example #foobar
     * @return boolean
     */
    public function postMessage(string $message, string $channel)
    {
        $ch = curl_init("https://slack.com/api/chat.postMessage");
        $data = [
            "token" => $this->token,
            "channel" => $channel,
            "text" => $message,
            "username" => $this->botName,
            "as_user" => false,
        ];
        if (strpos($this->botIcon, 'http') !== false) {
            $data['icon_url'] = $this->botIcon;
        } elseif (preg_match('/^([:\']).*\1$/m', $this->botIcon)) {
            $data['icon_emoji'] = $this->botIcon;
        }

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);
        var_dump($result);

        return $result;
    }
}