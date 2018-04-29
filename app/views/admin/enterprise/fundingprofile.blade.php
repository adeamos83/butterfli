					<tr>
						<td>
							<select autocomplete="off" name="funding_rule_type[<?php echo $i ?>]">
								<?php foreach ($fundingprofile->funding_rules() as $key => $value) { ?>
									<option value="<?php echo $key ?>"<?php if($fundingprofile->funding_rule_type == $key) { echo ' selected="selected"'; } ?>><?php echo $value ?></option><!-- key=<?php echo $key ?> funding_rule_type=<?php echo $fundingprofile->funding_rule_type ?> -->
								<?php } ?>
							</select>
						</td>
						<td>
							<select autocomplete="off" name="payment_type[<?php echo $i ?>]">
								<?php foreach ($fundingprofile->payment_types() as $key => $value) { ?>
									<option value="<?php echo $key ?>"<?php if($fundingprofile->payment_type == $key) { echo ' selected="selected"'; } ?>><?php echo $value ?></option>
								<?php } ?>
							</select>
						</td>
						
						<td class="text-right">
							<input type="hidden" name="funding_profile_id[<?php echo $i; ?>]" value="<?php echo $fundingprofile->id ?>"  />
							<input class="text-right" type="text" name="amount[<?php echo $i ?>]" value="<?php echo $fundingprofile->amount ?>" />
						</td>

						<td>
							<select autocomplete="off" name="bill_enterpriseclient_id[<?php echo $i ?>]">
								<?php foreach ($fundingprofile->billable_parties() as $key => $value) { ?>
									<option value="<?php echo $value->id ?>"<?php if($fundingprofile->bill_enterpriseclient_id == $value->id) { echo ' selected="selected"'; } ?>><?php echo $value->company ?></option>
								<?php } ?>
							</select>
						</td>
						<td class="text-center">
							<span>
								<input type="checkbox" name="delete_funding_profile[<?php echo $i; ?>]" value="1" data-displayid="#delete_funding_profile_display<?php echo $i; ?>" />
							</span>
						</td>
					</tr>
