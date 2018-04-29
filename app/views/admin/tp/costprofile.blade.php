					<tr>
						<td>
							<select class="narrow" autocomplete="off" name="service_type[<?php echo $i; ?>]">
								<?php foreach ($costprofile->service_types() as $key => $value) { ?>
									<?php if($value == null) { continue; } ?>
									<option value="<?php echo $key; ?>"<?php if($costprofile->service_type == $key) { echo ' selected="selected"'; } ?>><?php echo $value; ?></option>
								<?php } ?>
							</select>
						</td>
						<td class="text-right">
							<input type="hidden" name="rate_profile_id[<?php echo $i; ?>]" value="<?php echo $costprofile->id ?>"  />
							<input class="narrow text-right" type="text" name="base_fare[<?php echo $i; ?>]" value="<?php echo sprintf("%0.2f", $costprofile->base_fare); ?>" />
						</td>
						<td class="text-right">
							<input class="narrow text-right" type="text" name="per_mile[<?php echo $i; ?>]" value="<?php echo sprintf("%0.2f", $costprofile->per_mile); ?>" />
						</td>
						<td class="text-right">
							<input class="narrow text-right" type="text" name="included_mileage[<?php echo $i; ?>]" value="<?php echo sprintf("%0.2f", $costprofile->included_mileage); ?>" />
						</td>
						<td class="text-right">
							<input class="narrow text-right" type="text" name="commission_rate[<?php echo $i; ?>]" value="<?php echo sprintf("%0.2f", $costprofile->commission_rate); ?>" />
						</td>
						<td class="text-center">
							<span>
								<input type="checkbox" name="delete_rate_profile[<?php echo $i; ?>]" value="1" data-displayid="#delete_rate_profile_display<?php echo $i; ?>" />
							</span>
						</td>
					</tr>
