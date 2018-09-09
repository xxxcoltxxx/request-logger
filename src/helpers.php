<?php

if (! function_exists('request_logger')) {
    /**
     * Get request logger data provider.
     *
     * @return RequestLogger\RequestDataProvider
     */
    function request_logger()
    {
        return resolve(RequestLogger\RequestDataProvider::class);
    }
}
