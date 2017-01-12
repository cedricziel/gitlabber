<?php

namespace AppBundle\Factory;

use AppBundle\Entity\Gitlab\Host;
use AppBundle\Entity\Gitlab\Project;
use Buzz\Client\Curl;
use Gitlab\Client;

class GitlabApiFactory
{
    /**
     * @param Project $project
     *
     * @return Client
     */
    public static function fromProject(Project $project)
    {
        $host = $project->getHost();

        return static::fromHost($host);
    }

    /**
     * @param Host $host
     *
     * @return Client
     */
    public static function fromHost(Host $host) {
        $httpClient = new Curl();

        $client = new Client($host->getUrl(), $httpClient);
        $client->authenticate($host->getToken(), Client::AUTH_URL_TOKEN);

        return $client;
    }
}
