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
        $this->_url = $this->getMVUrl();

        $crawler = $this->client->request('GET', $this->_url . '/foro/');

        $titulos = $crawler->filter('.widecol .box');
        $this->mv = $titulos->filter('h3')->each(function($n, $i) use ($titulos) {
                $data['title'] = $n->text();
                $data['forums'] = $titulos->filter('.fpanels')->eq($i)->each(
                    function ($x) {
                        return $x->filter('.fpanel .info')->each(
                            function ($z) {
                                $foro[$z->filter('a')->first()->text()] = $z->filter('.sub a')->each(
                                    function ($r) {
                                        return $r->text();
                                    }
                                );
                                return $foro;
                            }
                        );
                    }
                );
                return $data;
            });

        $json = new JsonResponse(array('titles' => $this->mv));
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
    private function getMVUrl()
    {
        return $this->container->getParameter('mv.url');
    }
}
