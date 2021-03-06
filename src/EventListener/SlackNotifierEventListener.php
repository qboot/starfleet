<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\EventListener;

use App\Entity\Submit;
use App\Event\CfpEndingSoonEvent;
use App\Event\NewConferenceEvent;
use App\Event\NewConferencesEvent;
use App\Event\NewTalkSubmittedEvent;
use App\Event\SubmitStatusChangedEvent;
use App\SlackNotifier;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SlackNotifierEventListener implements EventSubscriberInterface
{
    private $slackNotifier;

    public function __construct(SlackNotifier $slackNotifier)
    {
        $this->slackNotifier = $slackNotifier;
    }

    public static function getSubscribedEvents()
    {
        return [
            NewTalkSubmittedEvent::class => 'onNewTalkSubmitted',
            NewConferenceEvent::class => 'onNewConferenceAdded',
            NewConferencesEvent::class => 'onNewConferencesAdded',
            SubmitStatusChangedEvent::class => 'onSubmitStatusChanged',
            CfpEndingSoonEvent::class => 'onCfpEndingSoon',
        ];
    }

    public function onNewTalkSubmitted(NewTalkSubmittedEvent $event)
    {
        $payload = SlackNotifier::EMPTY_PAYLOAD;
        array_push($payload['attachments'], $event->buildAttachment());
        $this->slackNotifier->notify($payload);
    }

    public function onNewConferenceAdded(NewConferenceEvent $event)
    {
        $payload = SlackNotifier::EMPTY_PAYLOAD;
        array_push($payload['attachments'], $event->buildAttachment());
        $this->slackNotifier->notify($payload);
    }

    public function onNewConferencesAdded(NewConferencesEvent $event)
    {
        $payload = SlackNotifier::EMPTY_PAYLOAD;
        $newConferences = $event->getNewConferences();
        $conferenceAttachment = SlackNotifier::ATTACHMENT;
        $conferenceAttachment['pretext'] = '✨  '.\count($newConferences).' nouvelles conférences ajoutées';

        foreach ($newConferences as $newConference) {
            $conferenceField = $event->buildAttachmentField($newConference);
            array_push($conferenceAttachment['fields'], $conferenceField);
        }

        array_push($payload['attachments'], $conferenceAttachment);
        $this->slackNotifier->notify($payload);
    }

    public function onSubmitStatusChanged(SubmitStatusChangedEvent $event)
    {
        if (Submit::STATUS_PENDING === $event->getSubmit()->getStatus()) {
            return;
        }

        $payload = SlackNotifier::EMPTY_PAYLOAD;
        array_push($payload['attachments'], $event->buildAttachment());
        $this->slackNotifier->notify($payload);
    }

    public function onCfpEndingSoon(CfpEndingSoonEvent $event)
    {
        $payload = SlackNotifier::EMPTY_PAYLOAD;
        array_push($payload['attachments'], $event->buildAttachment());
        $this->slackNotifier->notify($payload);
    }
}
