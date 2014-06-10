<?php

namespace Aegir\Hostmaster\Controller;

use Aegir\Hostmaster\Form\ProjectType;
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
class ProjectController extends FOSRestController
{
  const SESSION_CONTEXT_PROJECT = 'projects';

  /**
   * List all projects.
   *
   * @ApiDoc(
   *   resource = true,
   *   statusCodes = {
   *     200 = "Returned when successful"
   *   }
   * )
   *
   * @Annotations\QueryParam(name="offset", requirements="\d+", nullable=true, description="Offset from which to start listing projects.")
   * @Annotations\QueryParam(name="limit", requirements="\d+", default="5", description="How many projects to return.")
   *
   * @Annotations\View()
   *
   * @param Request               $request      the request object
   * @param ParamFetcherInterface $paramFetcher param fetcher service
   *
   * @return array
   */
  public function getProjectsAction(Request $request, ParamFetcherInterface $paramFetcher)
  {
    $session = $request->getSession();

    $offset = $paramFetcher->get('offset');
    $start = null == $offset ? 0 : $offset + 1;
    $limit = $paramFetcher->get('limit');

    $projects = $session->get(self::SESSION_CONTEXT_PROJECT, array());
    $projects = array_slice($projects, $start, $limit, true);

    return new ProjectCollection($projects, $offset, $limit);
  }

  /**
   * Get a single project.
   *
   * @ApiDoc(
   *   output = "Aegir\Provision\Model\Project",
   *   statusCodes = {
   *     200 = "Returned when successful",
   *     404 = "Returned when the project is not found"
   *   }
   * )
   *
   * @Annotations\View(templateVar="project")
   *
   * @param Request $request the request object
   * @param int     $id      the project id
   *
   * @return array
   *
   * @throws NotFoundHttpException when project not exist
   */
  public function getProjectAction(Request $request, $id)
  {
    $session = $request->getSession();
    $projects   = $session->get(self::SESSION_CONTEXT_PROJECT);
    if (!isset($projects[$id])) {
      throw $this->createNotFoundException("Project does not exist.");
    }

    $view = new View($projects[$id]);
    $group = $this->container->get('security.context')->isGranted('ROLE_API') ? 'restapi' : 'standard';
    $view->getSerializationContext()->setGroups(array('Default', $group));

    return $view;
  }

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
  public function newProjectAction()
  {
    return $this->createForm(new ProjectType());
  }

  /**
   * Creates a new project from the submitted data.
   *
   * @ApiDoc(
   *   resource = true,
   *   input = "Aegir\Hostmaster\Form\ProjectType",
   *   statusCodes = {
   *     200 = "Returned when successful",
   *     400 = "Returned when the form has errors"
   *   }
   * )
   *
   * @Annotations\View(
   *   template = "AegirHostmaster:Project:newProject.html.twig",
   *   statusCode = Codes::HTTP_BAD_REQUEST
   * )
   *
   * @param Request $request the request object
   *
   * @return FormTypeInterface|RouteRedirectView
   */
  public function postProjectsAction(Request $request)
  {
    $session = $request->getSession();
    $projects   = $session->get(self::SESSION_CONTEXT_PROJECT);

    $project = new Project();
    $project->id = $this->getValidIndex($projects);
    $form = $this->createForm(new ProjectType(), $project);

    $form->submit($request);
    if ($form->isValid()) {
      $project->secret = base64_encode($this->get('security.secure_random')->nextBytes(64));
      $projects[$project->id] = $project;
      $session->set(self::SESSION_CONTEXT_PROJECT, $projects);

      return $this->routeRedirectView('get_project', array('id' => $project->id));
    }

    return array(
      'form' => $form
    );
  }

  /**
   * Presents the form to use to update an existing project.
   *
   * @ApiDoc(
   *   resource = true,
   *   statusCodes={
   *     200 = "Returned when successful",
   *     404 = "Returned when the project is not found"
   *   }
   * )
   *
   * @Annotations\View()
   *
   * @param Request $request the request object
   * @param int     $id      the project id
   *
   * @return FormTypeInterface
   *
   * @throws NotFoundHttpException when project not exist
   */
  public function editProjectsAction(Request $request, $id)
  {
    $session = $request->getSession();

    $projects = $session->get(self::SESSION_CONTEXT_PROJECT);
    if (!isset($projects[$id])) {
      throw $this->createNotFoundException("Project does not exist.");
    }

    $form = $this->createForm(new ProjectType(), $projects[$id]);

    return $form;
  }

  /**
   * Update existing project from the submitted data or create a new project at a specific location.
   *
   * @ApiDoc(
   *   resource = true,
   *   input = "Aegir\Hostmaster\Form\ProjectType",
   *   statusCodes = {
   *     201 = "Returned when a new resource is created",
   *     204 = "Returned when successful",
   *     400 = "Returned when the form has errors"
   *   }
   * )
   *
   * @Annotations\View(
   *   template="AegirHostmaster:Project:editProject.html.twig",
   *   templateVar="form"
   * )
   *
   * @param Request $request the request object
   * @param int     $id      the project id
   *
   * @return FormTypeInterface|RouteRedirectView
   *
   * @throws NotFoundHttpException when project not exist
   */
  public function putProjectsAction(Request $request, $id)
  {
    $session = $request->getSession();

    $projects   = $session->get(self::SESSION_CONTEXT_PROJECT);
    if (!isset($projects[$id])) {
      $project = new Project();
      $project->id = $id;
      $statusCode = Codes::HTTP_CREATED;
    } else {
      $project = $projects[$id];
      $statusCode = Codes::HTTP_NO_CONTENT;
    }

    $form = $this->createForm(new ProjectType(), $project);

    $form->submit($request);
    if ($form->isValid()) {
      if (!isset($project->secret)) {
        $project->secret = base64_encode($this->get('security.secure_random')->nextBytes(64));
      }
      $projects[$id] = $project;
      $session->set(self::SESSION_CONTEXT_PROJECT, $projects);

      return $this->routeRedirectView('get_project', array('id' => $project->id), $statusCode);
    }

    return $form;
  }

  /**
   * Removes a project.
   *
   * @ApiDoc(
   *   resource = true,
   *   statusCodes={
   *     204="Returned when successful",
   *     404="Returned when the project is not found"
   *   }
   * )
   *
   * @param Request $request the request object
   * @param int     $id      the project id
   *
   * @return RouteRedirectView
   *
   * @throws NotFoundHttpException when project not exist
   */
  public function deleteProjectsAction(Request $request, $id)
  {
    $session = $request->getSession();
    $projects   = $session->get(self::SESSION_CONTEXT_PROJECT);
    if (!isset($projects[$id])) {
      throw $this->createNotFoundException("Project does not exist.");
    }

    unset($projects[$id]);
    $session->set(self::SESSION_CONTEXT_PROJECT, $projects);

    return $this->routeRedirectView('get_projects', array(), Codes::HTTP_NO_CONTENT);
  }

  /**
   * Removes a project.
   *
   * @ApiDoc(
   *   resource = true,
   *   statusCodes={
   *     204="Returned when successful",
   *     404="Returned when the project is not found"
   *   }
   * )
   *
   * @param Request $request the request object
   * @param int     $id      the project id
   *
   * @return RouteRedirectView
   *
   * @throws NotFoundHttpException when project not exist
   */
  public function removeProjectsAction(Request $request, $id)
  {
    return $this->deleteProjectsAction($request, $id);
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
