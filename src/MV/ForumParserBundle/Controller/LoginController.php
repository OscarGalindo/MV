<?php

namespace MV\ForumParserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Goutte\Client;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\BrowserKit;
use Symfony\Component\HttpFoundation\Request;

class LoginController extends Controller
{
    /**
     * @var string
     */
    private $_url = 'http://m.mediavida.com/login.php';

    /**
     * @var Client
     */
    private $_client;

    function __construct()
    {
        $this->_client = new Client();
    }

    /**
     * Login via POST
     *   POST:
     *      username string
     *      password string
     *
     * @return JsonResponse
     */
    public function loginAction(Request $request)
    {
        $data = array();
        $html = $this->_getHtml();

        $r = json_decode($request->getContent(), true);
        $user = strtolower($r['username']);
        $pass = $r['password'];
//        $user = strtolower('DarkSoldier'); // TEST IN LOCAL
//        $pass = 'password'; // TEST IN LOCAL

        $form = $html->selectButton('Entrar')->form();
        $data_crawler = $this->_client->submit($form, array('name' => $user, 'password' => $pass, 'cookie' => 1));

        $d = $data_crawler->filter('#userinfo');
        $user_html = strtolower($d->filter('a')->first()->text());

        $data['result'] = ($user_html == $user) ? true : false;
        $data['cookies'] = $this->_client->getRequest()->getCookies();
        $data['logout_url'] = $d->filter('a')->last()->link()->getUri();
        $data['nickname'] = $d->filter('a')->first()->text();

        return new JsonResponse($data);
    }

    public function logoutAction(Request $request)
    {
        $r = json_decode($request->getContent(), true);
        $url = strtolower($r['data_url']);
        $this->_client->request('GET', $url);
        return new JsonResponse(array('result' => false));
    }

    /**
     * Devuelve el crawler contenedor de la parte de login de MV
     *
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    private function _getHtml()
    {
        return $this->_client->request('GET', $this->_url);
    }
}
