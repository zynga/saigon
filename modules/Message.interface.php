<?php
//
// Copyright (c) 2015, Pinterest Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

interface Message
{
    // Return false if function is not used in your class
    public function messageByRooms($rooms, $message, $color, $notify);
}

