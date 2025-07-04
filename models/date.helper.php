<?php

class DateHelper {
    public static function init() {
        date_default_timezone_set('Asia/Manila');
    }

    public static function getDateTime() {
        self::init();
        return date('Y-m-d H:i:s');
    }

    public static function getDate() {
        self::init();
        return date('Y-m-d');
    }

    public static function getTime() {
        self::init();
        return date('H:i:s');
    }

    public static function formatDateTime($dateTime) {
        if (empty($dateTime)) return '';
        self::init();
        return date('Y-m-d H:i:s', strtotime($dateTime));
    }

    public static function formatDate($date) {
        if (empty($date)) return '';
        self::init();
        return date('Y-m-d', strtotime($date));
    }

    public static function formatTime($time) {
        if (empty($time)) return '';
        self::init();
        return date('H:i:s', strtotime($time));
    }
} 