<?php
// AppBundle/SitemapController.php
 
namespace AppBundle\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
 
class SitemapController extends Controller
{
    /**
     * @Route("/sitemap/sitemap.xml", name="sitemap", defaults={"_format"="xml"})
     */
    public function showAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $urls = array();
        $hostname = $request->getSchemeAndHttpHost();
 
        // add static urls
        $urls[] = array('loc' => $this->generateUrl('homepage'));
        $urls[] = array('loc' => $this->generateUrl('fos_user_security_login'));
        $urls[] = array('loc' => $this->generateUrl('user_register'));
         
        // add static urls with optional tags
        // add dynamic urls, like blog posts from your DB
        // add image urls
        // return response in XML format
        $response = new Response(
            $this->renderView('@App/sitemap/sitemap.html.twig', array( 'urls' => $urls,
                'hostname' => $hostname)),
            200
        );
        $response->headers->set('Content-Type', 'text/xml');
 
        return $response;
 
    }
 
}