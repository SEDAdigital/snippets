<?php

/**
 *
 * Servertimings
 *
 * @project:    snippets
 * @author:     Jens Külzer <jk@seda.digital>
 * @date:       2917-02-27 15:30
 * @copyright:  2017 SEDA.digital GmbH & Co. KG
 *
 */

switch($modx->event->name) {
    case 'OnWebPageInit':
    case 'OnLoadWebPageCache':
    case 'OnInitCulture':
    case 'OnLoadWebDocument':
    case 'OnWebPageComplete':   // this wont be visible in chrome devtools because headers are already sent...
    case 'OnWebPagePrerender':
        // quick exit /noop if not logged in
        if ($modx->user->id==0 || !$modx->resource ) {
            return;
        }

        if (!isset($modx->serverTiming)) $modx->serverTiming = array();
        $modx->serverTiming[$modx->event->name] = microtime(true);

        // quick if not last event
        if ( $modx->event->name!="OnWebPagePrerender" ) {
            return;
        }

        // calculate timing steps
        $timediffs = array();
        $diff = $modx->startTime;
        $idx=1;
        foreach ($modx->serverTiming as $e=>$time) {
            $timediffs[] = array( $idx.". ".$e => sprintf("%2.5F",  $time - $diff ));
            $diff = $time;
            $idx++;
        }

        // get existing resource headers
        $contentType = $modx->resource->getOne('ContentType');
        $headers = $contentType->get('headers');

        // get the timings, code from modresponse.class.php
        $totalTime= (microtime(true) - $modx->startTime);
        $queryTime= $modx->queryTime;
        $queries= isset ($modx->executedQueries) ? $modx->executedQueries : 0;
        $phpTime= $totalTime - $queryTime;
        $queryTime= sprintf("%2.5F", $queryTime);
        $totalTime= sprintf("%2.5F", $totalTime);
        $phpTime= sprintf("%2.5F", $phpTime);
        $source= $modx->resourceGenerated ? "DB" : "cache";

        $modxTimings = array();
        $modxTimings[] = array("MODX Total Time" => $totalTime);
        $modxTimings[] = array("PHP Time" => $phpTime);
        $modxTimings[] = array("Query Time (". $source .", #". $queries . ")" => $queryTime);

        $modxTimings = array_merge($modxTimings,$timediffs);

        $headers[] = "Server-Timing: ".implode(',', array_map( function($k, $v) {
                $n = array_keys($v);
                $t = array_values($v);
                return $k."=".$t[0]."; \"".$n[0]."\"";
            }, array_keys($modxTimings), $modxTimings));
        $contentType->set('headers', $headers);

        break;
}