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

				if ($destField) {
					echo "<script>
						$(document).ready(function() {
							console.log('Instant Concatenate Loaded');
							function concat() {
								var value = '';
								var src = " . json_encode($srcFields) . ";
								var space = '" . $space . "';
								var allValuesFilledOut = true;
								for (var i=0; i < src.length; i++) {
									if (i > 0) {
										value = value + space;
									}
									value = value + $('[name=\"'+src[i]+'\"]').val();
									if($('[name=\"'+src[i]+'\"]').val()==="")
									{
										console.log('Some field empty');
                                        allValuesFilledOut = allValuesFilledOut && false;
									}
									else
									{
										allValuesFilledOut = allValuesFilledOut && true;
									}
								}
								console.log('Concatenating to '+value);
								var destination = $('[name=\"" . $destField . "\"]');
								destination.val(value);
								
								// Trigger a change event for other modules, branching logic, etc.
								if(allValuesFilledOut == true)
								{
									console.log('All fields filled out');
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
