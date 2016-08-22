<?php

class HipChat implements Message
{

    public function __construct($from = 'Saigon')
    {
        $this->client = new \HipChat\HipChat(HIPCHAT_TOKEN);
        $this->from = $from;
    }

    public function messageByRooms($rooms, $message, $color = 'yellow', $notify = false)
    {
        if (!is_array($rooms)) {
            if (preg_match('/,/', $rooms)) {
                $rooms = preg_split('/\s?,\s?/', $rooms);
            }
            else {
                $rooms = array($rooms);
            }
        }
        foreach ($rooms as $room) {
            try {
                $this->client->message_room(
                    $room, $this->from, $message, $notify, $color
                );
            }
            catch (Exception $e) {
                error_log("Caught Exception: " . $e);
            }
        }
    }

}

