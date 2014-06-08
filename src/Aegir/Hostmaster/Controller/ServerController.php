<?php

namespace Aegir\Hostmaster\Controller;

use Aegir\Hostmaster\Form\ServerType;
use Aegir\Provision\Model\Server;
use Aegir\Provision\Model\ServerCollection;

use FOS\RestBundle\Util\Codes;

use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\RouteRedirectView;

use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Rest controller for servers
 *
 * @package Aegir\Hostmaster\Controller
 * @author Jon Pugh <jonpugh@live.com>
 */
class ServerController extends FOSRestController
{
  const SESSION_CONTEXT_SERVER = 'servers';

  /**
   * List all servers.
   *
   * @ApiDoc(
   *   resource = true,
   *   statusCodes = {
   *     200 = "Returned when successful"
   *   }
   * )
   *
   * @Annotations\QueryParam(name="offset", requirements="\d+", nullable=true, description="Offset from which to start listing servers.")
   * @Annotations\QueryParam(name="limit", requirements="\d+", default="5", description="How many servers to return.")
   *
   * @Annotations\View()
   *
   * @param Request               $request      the request object
   * @param ParamFetcherInterface $paramFetcher param fetcher service
   *
   * @return array
   */
  public function getServersAction(Request $request, ParamFetcherInterface $paramFetcher)
  {
    $session = $request->getSession();

    $offset = $paramFetcher->get('offset');
    $start = null == $offset ? 0 : $offset + 1;
    $limit = $paramFetcher->get('limit');

    $servers = $session->get(self::SESSION_CONTEXT_SERVER, array());
    $servers = array_slice($servers, $start, $limit, true);

    return new ServerCollection($servers, $offset, $limit);
  }

  /**
   * Get a single server.
   *
   * @ApiDoc(
   *   output = "Aegir\Provision\Model\Server",
   *   statusCodes = {
   *     200 = "Returned when successful",
   *     404 = "Returned when the server is not found"
   *   }
   * )
   *
   * @Annotations\View(templateVar="server")
   *
   * @param Request $request the request object
   * @param int     $id      the server id
   *
   * @return array
   *
   * @throws NotFoundHttpException when server not exist
   */
  public function getServerAction(Request $request, $id)
  {
    $session = $request->getSession();
    $servers   = $session->get(self::SESSION_CONTEXT_SERVER);
    if (!isset($servers[$id])) {
      throw $this->createNotFoundException("Server does not exist.");
    }

    $view = new View($servers[$id]);
    $group = $this->container->get('security.context')->isGranted('ROLE_API') ? 'restapi' : 'standard';
    $view->getSerializationContext()->setGroups(array('Default', $group));

    return $view;
  }

  /**
   * Presents the form to use to create a new server.
   *
   * @ApiDoc(
   *   resource = true,
   *   statusCodes = {
   *     200 = "Returned when successful"
   *   }
   * )
   *
   * @Annotations\View()
   *
   * @return FormTypeInterface
   */
  public function newServerAction()
  {
    return $this->createForm(new ServerType());
  }

  /**
   * Creates a new server from the submitted data.
   *
   * @ApiDoc(
   *   resource = true,
   *   input = "Aegir\Hostmaster\Form\ServerType",
   *   statusCodes = {
   *     200 = "Returned when successful",
   *     400 = "Returned when the form has errors"
   *   }
   * )
   *
   * @Annotations\View(
   *   template = "AegirHostmaster:Server:newServer.html.twig",
   *   statusCode = Codes::HTTP_BAD_REQUEST
   * )
   *
   * @param Request $request the request object
   *
   * @return FormTypeInterface|RouteRedirectView
   */
  public function postServersAction(Request $request)
  {
    $session = $request->getSession();
    $servers   = $session->get(self::SESSION_CONTEXT_SERVER);

    $server = new Server();
    $server->id = $this->getValidIndex($servers);
    $form = $this->createForm(new ServerType(), $server);

    $form->submit($request);
    if ($form->isValid()) {
      $server->secret = base64_encode($this->get('security.secure_random')->nextBytes(64));
      $servers[$server->id] = $server;
      $session->set(self::SESSION_CONTEXT_SERVER, $servers);

      return $this->routeRedirectView('get_server', array('id' => $server->id));
    }

    return array(
      'form' => $form
    );
  }

  /**
   * Presents the form to use to update an existing server.
   *
   * @ApiDoc(
   *   resource = true,
   *   statusCodes={
   *     200 = "Returned when successful",
   *     404 = "Returned when the server is not found"
   *   }
   * )
   *
   * @Annotations\View()
   *
   * @param Request $request the request object
   * @param int     $id      the server id
   *
   * @return FormTypeInterface
   *
   * @throws NotFoundHttpException when server not exist
   */
  public function editServersAction(Request $request, $id)
  {
    $session = $request->getSession();

    $servers = $session->get(self::SESSION_CONTEXT_SERVER);
    if (!isset($servers[$id])) {
      throw $this->createNotFoundException("Server does not exist.");
    }

    $form = $this->createForm(new ServerType(), $servers[$id]);

    return $form;
  }

  /**
   * Update existing server from the submitted data or create a new server at a specific location.
   *
   * @ApiDoc(
   *   resource = true,
   *   input = "Aegir\Hostmaster\Form\ServerType",
   *   statusCodes = {
   *     201 = "Returned when a new resource is created",
   *     204 = "Returned when successful",
   *     400 = "Returned when the form has errors"
   *   }
   * )
   *
   * @Annotations\View(
   *   template="AegirHostmaster:Server:editServer.html.twig",
   *   templateVar="form"
   * )
   *
   * @param Request $request the request object
   * @param int     $id      the server id
   *
   * @return FormTypeInterface|RouteRedirectView
   *
   * @throws NotFoundHttpException when server not exist
   */
  public function putServersAction(Request $request, $id)
  {
    $session = $request->getSession();

    $servers   = $session->get(self::SESSION_CONTEXT_SERVER);
    if (!isset($servers[$id])) {
      $server = new Server();
      $server->id = $id;
      $statusCode = Codes::HTTP_CREATED;
    } else {
      $server = $servers[$id];
      $statusCode = Codes::HTTP_NO_CONTENT;
    }

    $form = $this->createForm(new ServerType(), $server);

    $form->submit($request);
    if ($form->isValid()) {
      if (!isset($server->secret)) {
        $server->secret = base64_encode($this->get('security.secure_random')->nextBytes(64));
      }
      $servers[$id] = $server;
      $session->set(self::SESSION_CONTEXT_SERVER, $servers);

      return $this->routeRedirectView('get_server', array('id' => $server->id), $statusCode);
    }

    return $form;
  }

  /**
   * Removes a server.
   *
   * @ApiDoc(
   *   resource = true,
   *   statusCodes={
   *     204="Returned when successful",
   *     404="Returned when the server is not found"
   *   }
   * )
   *
   * @param Request $request the request object
   * @param int     $id      the server id
   *
   * @return RouteRedirectView
   *
   * @throws NotFoundHttpException when server not exist
   */
  public function deleteServersAction(Request $request, $id)
  {
    $session = $request->getSession();
    $servers   = $session->get(self::SESSION_CONTEXT_SERVER);
    if (!isset($servers[$id])) {
      throw $this->createNotFoundException("Server does not exist.");
    }

    unset($servers[$id]);
    $session->set(self::SESSION_CONTEXT_SERVER, $servers);

    return $this->routeRedirectView('get_servers', array(), Codes::HTTP_NO_CONTENT);
  }

  /**
   * Removes a server.
   *
   * @ApiDoc(
   *   resource = true,
   *   statusCodes={
   *     204="Returned when successful",
   *     404="Returned when the server is not found"
   *   }
   * )
   *
   * @param Request $request the request object
   * @param int     $id      the server id
   *
   * @return RouteRedirectView
   *
   * @throws NotFoundHttpException when server not exist
   */
  public function removeServersAction(Request $request, $id)
  {
    return $this->deleteServersAction($request, $id);
  }

  /**
   * Get a valid index key.
   *
   * @param array $servers
   *
   * @return int $id
   */
  private function getValidIndex($servers)
  {
    $id = count($servers);
    while (isset($servers[$id])) {
      $id++;
    }

    return $id;
  }

}
