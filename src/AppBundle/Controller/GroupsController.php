<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/groups")
 */
class GroupsController extends Controller
{
    /**
     * @Route()
     * @Template()
     */
    public function indexAction()
    {

    }
}
