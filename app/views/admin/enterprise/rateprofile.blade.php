					<tr>
						<td>
							<select class="narrow" autocomplete="off" name="service_type[<?php echo $i; ?>]">
								<?php foreach ($rateprofile->service_types() as $key => $value) { ?>
									<?php if($value == null) { continue; } ?>
									<option value="<?php echo $key; ?>"<?php if($rateprofile->service_type == $key) { echo ' selected="selected"'; } ?>><?php echo $value; ?></option>
								<?php } ?>
							</select>
						</td>
						<td class="text-right even">
							<input type="hidden" name="rate_profile_id[<?php echo $i; ?>]" value="<?php echo $rateprofile->id ?>"  />
							<input class="narrow text-right" type="text" name="base_fare[<?php echo $i; ?>]" value="<?php echo sprintf("%0.2f", $rateprofile->base_fare); ?>" />
						</td>
						<td class="text-right">
							<input class="narrow text-right" type="text" name="per_mile[<?php echo $i; ?>]" value="<?php echo sprintf("%0.2f", $rateprofile->per_mile); ?>" />
						</td>
						<td class="text-right even">
							<input class="narrow text-right" type="text" name="included_mileage[<?php echo $i; ?>]" value="<?php echo sprintf("%0.2f", $rateprofile->included_mileage); ?>" />
						</td>
						<td class="text-right">
							<input class="narrow text-right" type="text" name="deadhead_per_mile[<?php echo $i; ?>]" value="<?php echo sprintf("%0.2f", $rateprofile->deadhead_per_mile); ?>" />
						</td>
						<td class="text-right even">
							<input class="narrow text-right" type="text" name="deadhead_included_mileage[<?php echo $i; ?>]" value="<?php echo sprintf("%0.2f", $rateprofile->deadhead_included_mileage); ?>" />
						</td>
						<td class="text-right">
							<input class="narrow text-right" type="text" name="wait_time_per_minute[<?php echo $i; ?>]" value="<?php echo sprintf("%0.2f", $rateprofile->wait_time_per_minute); ?>" />
						</td>
						<td class="text-right even">
							<input class="narrow text-right" type="text" name="wait_time_included[<?php echo $i; ?>]" value="<?php echo sprintf("%0.2f", $rateprofile->wait_time_included); ?>" />
						</td>
						<td class="text-right">
							<span>
								<input type="checkbox" name="delete_rate_profile[<?php echo $i; ?>]" value="1" data-displayid="#delete_rate_profile_display<?php echo $i; ?>" />
							</span>
						</td>
					</tr>
					<tr>
						<td class="text-left">
							<span>TP Cost</span>
						</td>
						<td class="text-right even">
							<input class="narrow text-right" type="text" name="tpcost_base_fare[<?php echo $i; ?>]" value="<?php echo sprintf("%0.2f", $rateprofile->tpcost_base_fare); ?>" />
						</td>
						<td class="text-right">
							<input class="narrow text-right" type="text" name="tpcost_per_mile[<?php echo $i; ?>]" value="<?php echo sprintf("%0.2f", $rateprofile->tpcost_per_mile); ?>" />
						</td>
						<td class="even"></td>
						<td class="text-right">
							<input class="narrow text-right" type="text" name="tpcost_deadhead_per_mile[<?php echo $i; ?>]" value="<?php echo sprintf("%0.2f", $rateprofile->tpcost_deadhead_per_mile); ?>" />
						</td>
						<td class="even"></td>
						<td class="text-right">
							<input class="narrow text-right" type="text" name="tpcost_wait_time_per_minute[<?php echo $i; ?>]" value="<?php echo sprintf("%0.2f", $rateprofile->tpcost_wait_time_per_minute); ?>" />
						</td>
						<td class="even"></td>
						<td></td>
					</tr>
