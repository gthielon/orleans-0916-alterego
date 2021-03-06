<?php

namespace AlterEgoBundle\Controller;

use AlterEgoBundle\AlterEgoBundle;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use FOS\UserBundle\Model\UserInterface;
use AlterEgoBundle\Entity\Creneau;
use AlterEgoBundle\Entity\Activite;
use AlterEgoBundle\Entity\Reservation;
use Symfony\Component\HttpFoundation\Request;
use AlterEgoBundle\Calendar\CalendarEvent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Validator\Constraints\DateTime;
use AlterEgoBundle\Form\CheckType;
use AlterEgoBundle\Form\StartType;

/**
 * @Route("/coach", name="coach")
 */
class CoachController extends Controller
{
    /**
     * @Route("/", name="coach_index")
     */
    public function coachAction(Request $request)
    {
        $form = $this->createForm('AlterEgoBundle\Form\StartType');
        $form->handleRequest($request);
        $em = $this->getDoctrine()->getManager();
        $activites = $em->getRepository('AlterEgoBundle:Activite')->findByUser($this->getUser());

        if ($activites) {
            foreach ($activites as $activite) {
                foreach ($activite->getCreneaux() as $seance) {
                    $date = new \DateTime();
                    $date = $date->getTimestamp();
                    $seanceStamp = $seance->getDateheure()->getTimestamp() + ($seance->getDuree() * 60);


                    if (!isset($nextSeance) && ($seanceStamp >= $date)) {
                        $nextSeance = $seance;
                    }

                    if (isset($nextSeance) && $nextSeance->getDateheure() > $seance->getDateheure() && $seanceStamp >= $date) {
                        $nextSeance = $seance;
                    }
                }
            }
        }


        if ($form->isSubmitted() && $form->isValid() && isset($nextSeance)) {
            $em = $this->getDoctrine()->getManager();
            $nextSeance->setStartseance(1);
            $em->persist($nextSeance);
            $em->flush($nextSeance);

            return $this->redirectToRoute('checking', array('id' => $nextSeance->getId()));
        }

        if (isset($nextSeance)){return $this->render('AlterEgoBundle:Coach:coach.html.twig', array(
            'seance' => $nextSeance,
            'form' => $form->createView(),
            'activites' => $activites,

        ));

        }


        else {
            return $this->render('AlterEgoBundle:Coach:coach.html.twig', array(
                'seance' => [],
                'form' => $form->createView(),
                'activites' => $activites,
            ));
        }

    }

    /**
     * @Route("/activite")
     */
    public function coachActiviteAction()
    {
        return $this->render('AlterEgoBundle:Coach:coach_activite.html.twig');
    }


    /**
     * @Route("/planning", name="planning")
     */
    public function coachPlanningAction()
    {
        $em = $this->getDoctrine()->getManager();
        $activites = $em->getRepository('AlterEgoBundle:Activite')->findByUser($this->getUser());


        return $this->render('AlterEgoBundle:Coach:planning.html.twig', array(
            'activites' => $activites
        ));
    }

    /**
     * @Route("/cgu_coach", name="cgu_coach")
     */
    public function cguAction()
    {
        return $this->render('AlterEgoBundle:Coach:cgu_coach.html.twig');
    }
}