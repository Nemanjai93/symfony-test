<?php

namespace App\Controller;


use App\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class TaskController extends Controller {

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route(path="/new/event", name="post_event", methods={"POST"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function postEvent(Request $request) {
        $data = json_decode($request->getContent(), true);
        $message = ["Type"=>"Event","Country"=>$data['country'],"EventType"=>$data['event']];
        $rabbitMessage = json_encode($message);

        $this->get('old_sound_rabbit_mq.task_producer')->setContentType('application/json');
        $this->get('old_sound_rabbit_mq.task_producer')->publish($rabbitMessage);

        return new JsonResponse(array('Status' => 'OK'));
    }

    /**
     * @Route(path="/events.{_format}", name="event_list", defaults={"_format"="json"})
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getEvents(Request $request) {

        $encoders = array(new CsvEncoder(), new JsonEncoder());
        $normalizer = new ObjectNormalizer();

        $normalizer->setIgnoredAttributes(['date', 'id']);
        $serializer = new Serializer([$normalizer], $encoders);

        $events = $this->entityManager->getRepository(Event::class)
            ->lastSevenDaysEvents();
        $return_data = [];
        foreach ($events as $event) {
            $temp = $event;
            foreach ($events as $ev) {
                if ($event != $ev) {
                    if ($event->getCountry() == $ev->getCountry() && $event->getEvent() == $ev->getEvent()) {
                        $temp->setCount($temp->getCount() + $ev->getCount());
                    }
                }
            }
            foreach ($return_data as $repeat) {
                if ($repeat->getCountry() == $temp->getCountry() && $repeat->getEvent() == $temp->getEvent()) {
                    continue 2;
                }
            }
            $return_data[] = $temp;
        }

        $format = $request->getRequestFormat();

        $data = $serializer->serialize($return_data, $format);

        return new Response($data);

    }

}