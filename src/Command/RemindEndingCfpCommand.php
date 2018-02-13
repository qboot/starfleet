<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Command;

use App\Entity\Conference;
use App\Event\CfpEndingSoonEvent;
use App\Events;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RemindEndingCfpCommand extends Command
{
    const REMAINING_DAYS_STEPS = [30, 20, 10, 5, 1, 0];

    private $repository;
    private $dispatcher;

    public function __construct(RegistryInterface $doctrine, EventDispatcherInterface $dispatcher)
    {
        parent::__construct();

        $this->repository = $doctrine->getManager()->getRepository(Conference::class);
        $this->dispatcher = $dispatcher;
    }

    protected function configure()
    {
        $this->setName('starfleet-cfp-remind-ending');
        $this->setDescription('Trigger event for cfp ending soon');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $today = new \DateTime();
        $today->setTime(0, 0, 0);
        $conferences = $this->repository->findEndingCfps();

        foreach ($conferences as $conference) {
            $remainingDays = (int) ($conference->getCfpEndAt()->diff($today)->format('%a'));

            if (\in_array($remainingDays, self::REMAINING_DAYS_STEPS, true)) {
                $this->dispatcher->dispatch(Events::CFP_ENDING_SOON, new CfpEndingSoonEvent($conference, $remainingDays));
            }
        }
    }
}
