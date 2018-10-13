<?php

namespace App\Controller;


use App\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
        $events = $this->entityManager->getRepository(Event::class)
            ->lastSevenDaysEvents();

        $format = $request->getRequestFormat();

        //@todo: Format output to csv and json
        if ($format == 'csv') {
            $data = json_encode($events);
        }

        return $this->render('events.' . $format . '.twig', [
            'events' => $events
        ]);

    }

}