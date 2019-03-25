<?php
/**
 * All healper function goes here
 */


 function dd($args){
    echo '<pre>';
    die(var_dump(
        $args
    ));
    echo '</pre>';
 }

 function pr($arg){
    echo '<code>';
    echo '<pre>';
    print_r($arg);
    echo '</pre>';
    echo '</code>';
     
 }