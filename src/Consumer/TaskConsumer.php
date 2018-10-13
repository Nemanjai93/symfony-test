<?php

namespace App\Consumer;


use App\Entity\Event;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class TaskConsumer implements ConsumerInterface {

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * TaskConsumer constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }

    public function execute(AMQPMessage $msg)
    {

        $body = $msg->body;
        //var_dump($body);

        $response = json_decode($msg->body, true);

        $type = $response["Type"];

        if ($type == "Event") $this->saveEvent($response);

    }

    private function saveEvent($response) {

        $event = $this->entityManager->getRepository(Event::class)
            ->findOneBy([
                'country' => $response['Country'],
                'date' => new \DateTime('now'),
                'event' => $response['EventType']
            ]);

        if ($event == null) {
            $event = new Event();
            $event->setDate(new \DateTime('now'));
            $event->setCountry($response['Country']);
            $event->setEvent($response['EventType']);
        }
        $event->addCount();

        $this->entityManager->persist($event);
        $this->entityManager->flush();

    }

}