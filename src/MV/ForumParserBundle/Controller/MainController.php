<?php

namespace MV\ForumParserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Goutte\Client;
use Symfony\Component\HttpFoundation\Response;

class MainController extends Controller
{
    /**
     * @var string
     */
    private $_url;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var array
     */
    private $mv = array();

    public function __construct(){
        $this->client = new Client();
    }

    public function indexAction()
    {
        $data = $this->_getHtml();
        $this->mv = $data->filter('h3')->each(function($n) {
                $data['title'] = $n->text();
                return $data;
            });

        $json = new JsonResponse(array('MV' => $this->mv));
        $json->setEncodingOptions(128);
        return $json;
    }

    public function forumAction($slug_forum)
    {
        return Response::create($slug_forum);
    }


    /**
     * Devuelve la URL de MediaVida configurada en el config.yml del bundle
     *
     * @return string
     */
    private function _getMvUrl()
    {
        return $this->container->getParameter('mv.url');
    }

    /**
     * Devuelve el crawler contenedor de la parte de foros de MV
     *
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    private function _getHtml()
    {
        $this->_url = $this->_getMvUrl();
        $crawler = $this->client->request('GET', $this->_url . '/foro/');
        return $crawler->filter('.widecol .box');
    }
}
