<?php
/**
 * @file
 * Contains \Drupal\hello\Controller\HelloController.
 * 
 */

namespace Drupal\devjobss\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;
use Drupal\migrate\Plugin\migrate\process\FormatDate;

class Devjobss extends ControllerBase {
  public function content() {

    $title = \Drupal::request()->query->get('title');
    $location = \Drupal::request()->query->get('location');
    $checkbox = \Drupal::request()->query->get('full-time');

    $node_storage = \Drupal::entityTypeManager()->getStorage('node');

    /*
     if(! empty($title)){
      $conditions_array[] = ['field_job_title.value', $title];
    }
    if(! empty($location)){
      $conditions_array[] = ['field_country.value', $location];
    }
    if(count($conditions_array) > 0){
      $query = \Drupal::entityQuery('node');
      $group = $query->orConditionGroup()
        ->condition('field_job_title.value', $title)
        ->condition('field_country.value', $location);
      $nids = $query
        ->condition('type', 'jobs')
        ->condition($group)
        ->execute();
    }else{
      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
      $nids=$node_storage->getQuery()
      ->condition('type', 'jobs')
      ->execute();
    }
    */
    if(empty($title) && empty($location) ){
      $nids = $node_storage->getQuery()
      ->condition('status', 1)
      ->condition('type', 'jobs')
      ->execute();
    } else if(!empty($title) && !empty($location) ) {
        $nids = $node_storage->getQuery()
          ->condition('status', 1)
          ->condition('type', 'jobs')
          ->condition('field_job_title', $title, 'CONTAINS')
          ->condition('field_country', $location, 'CONTAINS')
          ->sort('nid', 'ASC')
          ->execute();
      }else if(!empty($title)) {
        $nids = $node_storage->getQuery()
          ->condition('status', 1)
          ->condition('type', 'jobs')
          ->condition('field_job_title', $title, 'CONTAINS')
          ->sort('nid', 'ASC')
          ->execute();
      } else if(!empty($location)) {
        $nids = $node_storage->getQuery()
          ->condition('type', 'jobs')
          ->condition('field_country', $location, 'CONTAINS')
          ->sort('nid', 'ASC')
          ->execute();
      }

    $results = Node::loadMultiple($nids);
    // dump($results);
    // die;
    $jobs =[];
    foreach ($results as $key => $nid) {
      //$node = Node::load($nid);
      $target_id = $nid->field_company_logo->getValue()[0]['target_id'];
      $file = File::load($target_id);
      $image_uri = $file->getFileUri();
      $style = ImageStyle::load('thumbnail');
      $url = $style->buildUrl($image_uri);
      
      $jobs[]=[
        'title' => $nid->getTitle(),
        'body'=>$nid->field_text	,
        'companyname'=>$nid->field_company_name->value,	
        'jobname'=>$nid->field_job_title->value,
        'country'=>$nid->field_country->value,
        'url'=>$url,
        'link' => $key,
      ];
  
    }
    
    return [
      // Your theme hook name.
      '#theme' => 'devjobss',
      '#jobs' => $jobs,
      
    ];
  }
}


