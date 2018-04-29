<div class="col-xs-12" style="font-size:12px;">
    <h2 style="text-align:center;border-bottom:1px solid#000;line-height:0.3em;margin: 10px 0 20px;"><span style="background:#fff;padding:0 10px;"><strong style="font-size:15px;">Estimated Fare Breakdown</strong></span></h2>
    <br>
    <table style="width: 100%;">
        <tr>
            <td style="font-size: 12pt;">
                Pick-up fee
            </td>
            <td style="text-align:right; font-size: 12pt;">
                <div><?php echo sprintf("$%0.2f", $estimate["base_price"]) ?></div>
            </td>
        </tr>
        <tr>
            <td style="font-size: 12pt;">
                <?php echo sprintf("%0.2f miles", $estimate["distance"]) ?> miles @ <?php echo sprintf("$%0.2f/mile", $estimate["per_mile_price"]) ?> 
            </td>
            <td style="text-align:right; font-size: 12pt;">
                <div><?php echo sprintf("$%0.2f", $estimate["mileage_price"]) ?></div>
            </td>
        </tr>
        <?php if($estimate["is_wheelchair"] == 1) { ?>
            <tr>
                <td style="font-size: 12pt; font-size: 12pt;">
                    <div id="is_wheelchair" style="display:none;">Wheelchair</div>
                </td>
                <td style="text-align:right; font-size: 12pt;">
                    <div><?php echo sprintf("$%0.2f", $estimate["wheelchair_price"]) ?></div>
                </td>
            </tr>
        <?php } ?>
        <?php if($estimate["is_wheelchair"] == 1) { ?>
            <tr>
                <td style="font-size: 12pt;">
                    <div id="is_oxygen_mask" style="display:none;">Oxygen Mask</div>
                </td>
                <td style="text-align:right; font-size: 12pt;">
                    <div><?php echo sprintf("$%0.2f", $estimate["oxygen_mask_price"]) ?></div>
                </td>
            </tr>
        <?php } ?>
        <tr>
            <td>
                <hr>
            </td>
            <td>
                <hr>
            </td>
        </tr>
        <tr>
            <td style="font-size: 12pt;">
                Subtotal
            </td>
            <td style="text-align:right; font-size: 12pt;">
                <div><?php echo sprintf("$%0.2f", $estimate["ride_estimated_price"]) ?></div>
            </td>
        </tr>
        <?php if($estimate["is_roundtrip"] == 1) { ?>
            <tr>
                <td style="font-size: 12pt;">
                    Round-trip Estimate
                </td>
            </tr>
        <?php } ?>
    </table>
</div>
