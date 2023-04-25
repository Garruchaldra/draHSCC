<?php

/**
 * Amazon aws video transcript support trait
 *
 * @category   Admin
 * @package    AWS
 * @author     Andy Sodt <asodt@uw.edu>
 * @copyright  2018 University of Washington
 * @license    https://opensource.org/licenses/MIT  MIT License
 * @version    git: $Id$
 */

/**
 * Amazon aws trait.
 */
trait AWSTranscriptSupport {

	/**
     * convert transcript to text
     *
     * @param json $transcript aws gernerated transcript in json
     * @return string text
     */
    public static function transcript_to_text($json) {
		return $json->results->transcripts[0]->transcript;
	}

    /**
     * convert transcript to webvtt
     *
     * @param json $transcript aws gernerated transcript in json
     * @return string webvtt
     */
    public static function transcript_to_vtt($json) {

        $vtt = "WEBVTT\n\n";

        $cue_id = 0;
        $cue_text = '';
        $start_time = 0;
        $end_time = 0;

        foreach ($json->results->items as $item) {

            // new cue
            if ($cue_id == 0) {
				$cue_id++;
				$start_time = $item->start_time;
				$end_time = $item->end_time;
				$cue_text = $item->alternatives[0]->content;
            }

            if ($item->type == 'punctuation') {
                $cue_text .= $item->alternatives[0]->content;
            } else {
                if (floatval($item->start_time) - floatval($end_time) > 2) {

					// end cue
					$vtt .= $cue_id . "\n";
					$vtt .= AWSTranscriptSupport::format_time_string($start_time, $end_time);
					$vtt .= $cue_text;
					$vtt .= "\n\n";

					// new cue
					$cue_id++;
					$start_time = $item->start_time;
					$end_time = $item->end_time;
					$cue_text = $item->alternatives[0]->content;				
                } else {

					// add to cue
                    $cue_text .= " " . $item->alternatives[0]->content;
                    $end_time = $item->end_time;
                }
            }
        }

		// complete last cue
		$vtt .= $cue_id . "\n";
        $vtt .= AWSTranscriptSupport::format_time_string($start_time, $end_time);
        $vtt .= $cue_text;
        $vtt .= "\n";

        return $vtt;
    }

	static function format_time_string($start_time, $end_time) {
		return AWSTranscriptSupport::format_video_time($start_time) . ' --> ' . AWSTranscriptSupport::format_video_time($end_time) . "\n";
	}

    static function format_video_time($time_string) {
        $time = floatval($time_string);
        $hours = floor($time / 60 / 60);
        $minutes = floor($time / 60);
        $seconds = $time - (($hours * 60 * 60) + ($minutes * 60));

        return str_pad($hours, 2, "0", STR_PAD_LEFT) . ":" . str_pad($minutes, 2, "0", STR_PAD_LEFT) . ":" . str_pad(number_format($seconds, 3, '.', ''), 6, "0", STR_PAD_LEFT);
    }

}
