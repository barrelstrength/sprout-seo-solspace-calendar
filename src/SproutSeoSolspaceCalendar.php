<?php

namespace barrelstrength\sproutseosolspacecalendar;

use barrelstrength\sproutbaseuris\events\RegisterUrlEnabledSectionTypesEvent;
use barrelstrength\sproutbaseuris\services\UrlEnabledSections;
use barrelstrength\sproutseosolspacecalendar\integrations\sproutseo\sectiontypes\SolspaceCalendarEvent;
use craft\base\Plugin;
use yii\base\Event;

/**
 * Class sproutseosolspacecalendar
 *
 * @package barrelstrength\sproutseosolspacecalendar
 */
class SproutSeoSolspaceCalendar extends Plugin
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        Event::on(UrlEnabledSections::class, UrlEnabledSections::EVENT_REGISTER_URL_ENABLED_SECTION_TYPES, static function(RegisterUrlEnabledSectionTypesEvent $event) {
            $event->urlEnabledSectionTypes[] = SolspaceCalendarEvent::class;
        });
    }

}
