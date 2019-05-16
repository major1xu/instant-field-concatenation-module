<?php namespace Vanderbilt\InstantConcatenateExternalModule;

use ExternalModules\AbstractExternalModule;
use ExternalModules\ExternalModules;

class InstantConcatenateExternalModule extends AbstractExternalModule
{
	function hook_data_entry_form($project_id, $record, $instrument, $event_id, $group_id) {
		$this->concatenate($project_id, $record, $instrument, $event_id, $group_id);
	}

	function hook_survey_page($project_id, $record, $instrument, $event_id, $group_id, $survey_hash, $response_id) {
		$this->concatenate($project_id, $record, $instrument, $event_id, $group_id);
	}

	function concatenate($project_id, $record, $instrument, $event_id, $group_id, $survey_hash = NULL, $response_id = NULL) {
		if ($project_id) {
			# get the specifications
			foreach($this->getSubSettings('concatenated-fields') as $field_data) {
				$destField = $field_data['destination'];
				$srcFields = $field_data['source'];
				if (!is_array($srcFields)) {
					$srcFields = array($srcFields);
				}
				$spaces = $field_data['spaces'];
				$space = "";
				if ($spaces) {
					$space = " ";
				}

				$fireEventAfterFillOutAllFields=$field_data['fire-event-after-filloutallfields'];
				$fireEventFlag = false;
                                if($fireEventAfterFillOutAllFields) {
				   $fireEventFlag=true;
				}

				if ($destField) {
					echo "<script>
						$(document).ready(function() {
							console.log('Instant Concatenate Loaded');
							function concat() {
								var value = '';
								var src = " . json_encode($srcFields) . ";
								var space = '" . $space . "';
                                var flag = " . json_encode($fireEventFlag) . "; 
                                console.log('flag:' + flag);
								var allValuesFilledOut = true;
								console.log('src.length: ' + src.length);
								for (var i=0; i < src.length; i++) {
									if (i > 0) {
										value = value + space;
									}
									if( flag ) {
										if( $('[name=\"'+src[i]+'\"]').val() )
										{
											allValuesFilledOut =  allValuesFilledOut && true;
										}
										else
										{
											allValuesFilledOut =  allValuesFilledOut && false;
										}
									}
									value = value + $('[name=\"'+src[i]+'\"]').val();	
								}
								console.log('Concatenating to '+value);
								var destination = $('[name=\"" . $destField . "\"]');
								destination.val(value);
								
								// Trigger a change event for other modules, branching logic, etc.
								if(flag) {
									if(allValuesFilledOut == true)
									{
										console.log('All fields filled out, fire a change event');
										destination.change();
									}
								}
								else
								{
									destination.change();
								}
							}";
					foreach ($srcFields as $src) {
						echo "$('[name=\"" . $src . "\"]').change(function() { concat(); }); ";
					}
					echo " });\n";
					echo "</script>";
				}
			}
		}
	}
}
