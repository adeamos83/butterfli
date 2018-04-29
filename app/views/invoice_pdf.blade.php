@extends('layoutpdf')
@section('content')
<style>
    .spa{
        width: 100%;
    }
    .half{
        width: 50%;
    }
</style>
<div class="page-content">
    <div class="box box-info tbl-box">
        <table width="100%">
            <tbody>
                <tr align="left">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td><h3>Invoice Issue {{ Config::get('app.website_title') }}</h3>
                    </td>
                </tr>
                <tr align="left">
                    <th>Tax Point Date</th>
                    <th>Invoice Number</th>
                    <th>Description</th>
                    <th>Week Total</th>
                    <th>Week EndDate</th>
                    <th>{{ Config::get('app.generic_keywords.Provider') }} payable Amount</th>
                    <th>{{ Config::get('app.generic_keywords.Provider') }} refundable Amount</th>
                    <th>Net Amount</th>
                </tr>
                <?php
                /* $settings = Settings::where('key', 'rider_fee')->first();
                  if (isset($settings->value)) {
                  $unit = $settings->value;
                  } else {
                  $unit = 1;
                  } */
                ?>
                <tr>
                    <td><?= $trips; ?></td>
                    <td><?php echo Config::get('app.website_title') . " " . Date('y-m') . $id; ?></td>
                    <td>Transportation service</td>
                    <td>
                        <?= Config::get('app.currency_symb') . sprintf2($total, 2); ?>
                    </td>
                    <td><?= $weekend; ?></td>
                    <td><?= $pay_to_provider; ?></td>
                    <td><?= $take_from_provider; ?></td>
                    <td>
                        <?= Config::get('app.currency_symb') . sprintf2(($total - $pay_to_provider + $take_from_provider), 2); ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="8"><hr style="color: black;"></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>Total Net</td>
                    <td><?= Config::get('app.currency_symb') . sprintf2(($total - $pay_to_provider + $take_from_provider), 2); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@stop