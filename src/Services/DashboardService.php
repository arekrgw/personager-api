<?php

namespace Src\Services;

class DashboardService
{
  public static $db = null;

  public static function getDashboard()
  {
    $reminders = RemindersService::getDashboard();
    if (isset($reminders["error"])) {
      return false;
    }

    $events = EventsService::getDashboard();
    if (isset($events["error"])) {
      return false;
    }

    return array("reminders" => $reminders, "events" => $events);
  }
}
