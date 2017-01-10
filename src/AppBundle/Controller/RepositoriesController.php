<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class RepositoriesController extends Controller
{
    /**
     * @Route("/", name="repositories")
     * @Template()
     * @param Request $request
     *
     * @return array
     */
    public function indexAction(Request $request)
    {
        $gitlabComApi = $this->get('zeichen32_gitlabapi.client.gitlabcom');
        $gitlabLocalApi = $this->get('zeichen32_gitlabapi.client.local');

        $localRepositories = $gitlabLocalApi->projects->all(1, 500);
        $remoteRepositories = $gitlabComApi->projects->accessible(1, 500);

        dump($localRepositories);

        return [
            'remoteRepositories' => $remoteRepositories,
            'localRepositories'  => $localRepositories,
        ];
    }
}
