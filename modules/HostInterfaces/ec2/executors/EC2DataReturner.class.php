<?php

class EC2DataReturner extends EC2Observer
{

    public function execute($response)
    {
        return $response;
    }
}

