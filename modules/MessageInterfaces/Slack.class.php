<?php

class Slack implements Message
{

    public function __construct($from = 'Saigon')
    {
        $client = new \Slack\Client(SLACK_TEAM, SLACK_TOKEN);
        $this->client = new \Slack\Notifier($client);
        $this->from = $from;
    }

    public function messageByRooms($rooms, $message, $color = 'yellow', $notify = false)
    {
        $emoji;
        $rgbcolor;
        if ( $color == 'yellow' ) {
            $emoji = ':construction:';
            $rgbcolor = '#FFFF00';
        }
        elseif ( $color == 'green' ) {
            $emoji = ':shipit:';
            $rgbcolor = '#008000';
        }
        else {
            $emoji = ':no_entry:';
            $rgbcolor = '#FF0000';
        }
        $slack_message = new \Slack\Message\Message();
        $slack_attachment = new \Slack\Message\MessageAttachment();
        $slack_attachment->setText($message)->setColor($rgbcolor);
        $slack_message->addAttachment($slack_attachment);
        if (!is_array($rooms)) {
            if (preg_match('/,/', $rooms)) {
                $rooms = preg_split('/\s?,\s?/', $rooms);
            }
            else {
                $rooms = array($rooms);
            }
        }
        foreach ($rooms as $room) {
            if (!preg_match('/^#/', $room)) {
                $room = '#' . $room;
            }
            $slack_message->setChannel($room)
                ->setIconEmoji($emoji)
                ->setUsername($this->from);
            try {
                $this->client->notify($slack_message);
            }
            catch (Exception $e) {
                error_log("Caught Exception: " . $e);
            }
        }
    }

}

