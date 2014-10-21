<?php

namespace MV\ForumParserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\BrowserKit\Cookie;
use Goutte\Client;

class NotificationsController extends Controller
{
    /**
     * @var string
     */
    private $_url = 'http://m.mediavida.com/notificaciones';

    /**
     * @var Client
     */
    private $client;

    public function __construct(){
        $this->client = new Client();
    }

    public function getNotificationsAction()
    {
        $data = array();
        $req = Request::createFromGlobals();

        $cookies = json_decode($req->request->get('cookies'));
        $crawler = $this->_getHtml($cookies);

        $data['avs'] = $this->extractNotifications($crawler->filter('a')->eq(1));
        $data['fav'] = $this->extractNotifications($crawler->filter('a')->eq(2));
        $data['msj'] = $this->extractNotifications($crawler->filter('a')->eq(3));

        return new JsonResponse($data);
    }

    private function extractNotifications(Crawler $crawler) {
        $notif = $crawler->filter('strong');
        if($notif->count() == 1) {
            return $notif->text();
        }
        return 0;
    }

    /**
     * Devuelve el crawler contenedor de la parte de foros de MV
     *
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    private function _getHtml($cookies)
    {
        foreach($cookies as $name=>$value) {
            $cookies = new Cookie($name, $value);
            $this->client->getCookieJar()->set($cookies);
        }

        $crawler = $this->client->request('GET', $this->_url);
        return $crawler->filter('#userinfo');
    }
}
