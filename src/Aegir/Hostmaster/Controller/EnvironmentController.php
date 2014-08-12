<?php

namespace Aegir\Hostmaster\Controller;

use Aegir\Hostmaster\Form\EnvironmentType;
use Aegir\Provision\Model\Environment;
use Aegir\Provision\Model\Project;
use Aegir\Provision\Model\ProjectCollection;

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
 * Rest controller for projects
 *
 * @package Aegir\Hostmaster\Controller
 * @author Jon Pugh <jonpugh@live.com>
 */
class EnvironmentController extends FOSRestController
{
  const SESSION_CONTEXT_ENVIRONMENTS = 'environments';

  /**
   * Presents the form to use to create a new project.
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
  public function newEnvironmentAction()
  {
    return $this->createForm(new EnvironmentType());
  }

  /**
   * Creates a new project from the submitted data.
   *
   * @ApiDoc(
   *   resource = true,
   *   input = "Aegir\Hostmaster\Form\EnvironmentType",
   *   statusCodes = {
   *     200 = "Returned when successful",
   *     400 = "Returned when the form has errors"
   *   }
   * )
   *
   * @Annotations\View(
   *   template = "AegirHostmaster:Project:newEnvironment.html.twig",
   *   statusCode = Codes::HTTP_BAD_REQUEST
   * )
   *
   * @param Request $request the request object
   *
   * @return FormTypeInterface|RouteRedirectView
   */
  public function postEnvironmentsAction(Request $request)
  {
    $session = $request->getSession();
    $environments   = $session->get(self::SESSION_CONTEXT_ENVIRONMENTS);

    $environment = new Environment();
    $environment->id = $this->getValidIndex($environments);
    $form = $this->createForm(new EnvironmentType(), $environment);

    $form->submit($request);
    if ($form->isValid()) {
      $environment->secret = base64_encode($this->get('security.secure_random')->nextBytes(64));
      $environments[$environment->id] = $environments;
      $session->set(self::SESSION_CONTEXT_ENVIRONMENTS, $environments);

      return $this->routeRedirectView('get_environment', array('id' => $environment->id));
    }

    return array(
      'form' => $form
    );
  }

  /**
   * Presents the form to use to update an existing environment.
   *
   * @ApiDoc(
   *   resource = true,
   *   statusCodes={
   *     200 = "Returned when successful",
   *     404 = "Returned when the environment is not found"
   *   }
   * )
   *
   * @Annotations\View()
   *
   * @param Request $request the request object
   * @param int     $id      the environment id
   *
   * @return FormTypeInterface
   *
   * @throws NotFoundHttpException when project not exist
   */
  public function editEnvironmentsAction(Request $request, $id)
  {
    $session = $request->getSession();

    $environments = $session->get(self::SESSION_CONTEXT_ENVIRONMENTS);
    if (!isset($environments[$id])) {
      throw $this->createNotFoundException("Environment does not exist.");
    }

    $form = $this->createForm(new EnvironmentType(), $environments[$id]);

    return $form;
  }

  /**
   * Update existing environment from the submitted data or create a new environment at a specific location.
   *
   * @ApiDoc(
   *   resource = true,
   *   input = "Aegir\Hostmaster\Form\EnvironmentType",
   *   statusCodes = {
   *     201 = "Returned when a new resource is created",
   *     204 = "Returned when successful",
   *     400 = "Returned when the form has errors"
   *   }
   * )
   *
   * @Annotations\View(
   *   template="AegirHostmaster:Project:editEnvironment.html.twig",
   *   templateVar="form"
   * )
   *
   * @param Request $request the request object
   * @param int     $id      the project id
   *
   * @return FormTypeInterface|RouteRedirectView
   *
   * @throws NotFoundHttpException when environment does not exist
   */
  public function putEnvironmentsAction(Request $request, $id)
  {
    $session = $request->getSession();

    $environments   = $session->get(self::SESSION_CONTEXT_PROJECT);
    if (!isset($projects[$id])) {
      $environment = new Environment();
      $environment->id = $id;
      $statusCode = Codes::HTTP_CREATED;
    } else {
      $environment = $environments[$id];
      $statusCode = Codes::HTTP_NO_CONTENT;
    }

    $form = $this->createForm(new EnvironmentType(), $environment);

    $form->submit($request);
    if ($form->isValid()) {
      if (!isset($environment->secret)) {
        $environment->secret = base64_encode($this->get('security.secure_random')->nextBytes(64));
      }
      $projects[$id] = $environment;
      $session->set(self::SESSION_CONTEXT_PROJECT, $environments);

      return $this->routeRedirectView('get_project', array('id' => $environment->id), $statusCode);
    }

    return $form;
  }

  /**
   * Removes an environment.
   *
   * @ApiDoc(
   *   resource = true,
   *   statusCodes={
   *     204="Returned when successful",
   *     404="Returned when the environment is not found"
   *   }
   * )
   *
   * @param Request $request the request object
   * @param int     $id      the environment id
   *
   * @return RouteRedirectView
   *
   * @throws NotFoundHttpException when project not exist
   */
  public function deleteEnvironmentAction(Request $request, $id)
  {
    $session = $request->getSession();
    $environments   = $session->get(self::SESSION_CONTEXT_ENVIRONMENTS);
    if (!isset($environments[$id])) {
      throw $this->createNotFoundException("Environment does not exist.");
    }

    unset($environments[$id]);
    $session->set(self::SESSION_CONTEXT_ENVIRONMENTS, $environments);

    return $this->routeRedirectView('get_project', array('id' => $environment_id), Codes::HTTP_NO_CONTENT);
  }

  /**
   * Removes a project.
   *
   * @ApiDoc(
   *   resource = true,
   *   statusCodes={
   *     204="Returned when successful",
   *     404="Returned when the environment is not found"
   *   }
   * )
   *
   * @param Request $request the request object
   * @param int     $id      the environment id
   *
   * @return RouteRedirectView
   *
   * @throws NotFoundHttpException when environment does not exist
   */
  public function removeEnvironmentsAction(Request $request, $id)
  {
    return $this->deleteEnvironmentAction($request, $id);
  }

  /**
   * Get a valid index key.
   *
   * @param array $projects
   *
   * @return int $id
   */
  private function getValidIndex($projects)
  {
    $id = count($projects);
    while (isset($projects[$id])) {
      $id++;
    }

    return $id;
  }

}
