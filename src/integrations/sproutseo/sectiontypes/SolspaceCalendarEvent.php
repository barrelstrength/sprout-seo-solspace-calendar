<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseosolspacecalendar\integrations\sproutseo\sectiontypes;

use barrelstrength\sproutbaseuris\base\UrlEnabledSectionType;
use barrelstrength\sproutbaseuris\models\UrlEnabledSection;
use craft\queue\jobs\ResaveElements;
use Craft;
use Solspace\Calendar\Calendar;
use Solspace\Calendar\Elements\Event as EventElement;
use Solspace\Calendar\Models\CalendarModel;

class SolspaceCalendarEvent extends UrlEnabledSectionType
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Events';
    }

    /**
     * @return string
     */
    public function getElementIdColumnName(): string
    {
        return 'setCalendarId';
    }

    /**
     * @return string
     */
    public function getUrlFormatIdColumnName(): string
    {
        return 'calendarId';
    }

    /**
     * @param $id
     *
     * @return CalendarModel|null
     */
    public function getById($id)
    {
        return Calendar::getInstance()->calendars->getCalendarById($id);
    }

    /**
     * @param $id
     *
     * @return array|\craft\base\Model|\craft\models\EntryType[]|null
     */
    public function getFieldLayoutSettingsObject($id)
    {
        return $this->getById($id);
    }

    /**
     * @return string
     */
    public function getElementTableName(): string
    {
        return 'calendar_events';
    }

    /**
     * @return string
     */
    public function getElementType(): string
    {
        return EventElement::class;
    }

    /**
     * @inheritdoc
     */
    public function getElementLiveStatus()
    {
        return EventElement::STATUS_ENABLED;
    }

    /**
     * @return string
     */
    public function getMatchedElementVariable(): string
    {
        return 'event';
    }

    /**
     * @param $siteId
     *
     * @return UrlEnabledSection[]
     */
    public function getAllUrlEnabledSections($siteId): array
    {
        $urlEnabledSections = [];

        $calendars = Calendar::getInstance()->calendars->getAllCalendars();

        foreach ($calendars as $calendar) {
            $siteSettings = $calendar->getSiteSettings();

            foreach ($siteSettings as $siteSetting) {
                if ($siteId == $siteSetting->siteId && $siteSetting->hasUrls) {
                    $urlEnabledSections[] = $calendar;
                }
            }
        }

        return $urlEnabledSections;
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return 'calendar_calendar_sites';
    }

    /**
     * @inheritdoc
     */
    public function resaveElements($elementGroupId = null): bool
    {
        if (!$elementGroupId) {
            return false;
        }

        $calendar = Calendar::getInstance()->calendars->getCalendarById($elementGroupId);

        if (!$calendar) {
            return false;
        }

        $siteSettings = $calendar->getSiteSettings();

        if (!$siteSettings) {
            return false;
        }

        // let's take the first site
        $primarySite = reset($siteSettings)->siteId ?? null;

        if (!$primarySite) {
            return false;
        }

        Craft::$app->getQueue()->push(new ResaveElements([
            'description' => 'Re-saving Event metadata',
            'elementType' => EventElement::class,
            'criteria' => [
                'siteId' => $primarySite,
                'calendarId' => $elementGroupId,
                'status' => null,
                'enabledForSite' => false,
                'limit' => null,
            ]
        ]));

        return true;
    }
}
