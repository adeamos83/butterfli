
<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>API Reference</title>

    <style>
      .highlight table td { padding: 5px; }
.highlight table pre { margin: 0; }
.highlight .gh {
  color: #999999;
}
.highlight .sr {
  color: #f6aa11;
}
.highlight .go {
  color: #888888;
}
.highlight .gp {
  color: #555555;
}
.highlight .gs {
}
.highlight .gu {
  color: #aaaaaa;
}
.highlight .nb {
  color: #f6aa11;
}
.highlight .cm {
  color: #75715e;
}
.highlight .cp {
  color: #75715e;
}
.highlight .c1 {
  color: #75715e;
}
.highlight .cs {
  color: #75715e;
}
.highlight .c, .highlight .cd {
  color: #75715e;
}
.highlight .err {
  color: #960050;
}
.highlight .gr {
  color: #960050;
}
.highlight .gt {
  color: #960050;
}
.highlight .gd {
  color: #49483e;
}
.highlight .gi {
  color: #49483e;
}
.highlight .ge {
  color: #49483e;
}
.highlight .kc {
  color: #66d9ef;
}
.highlight .kd {
  color: #66d9ef;
}
.highlight .kr {
  color: #66d9ef;
}
.highlight .no {
  color: #66d9ef;
}
.highlight .kt {
  color: #66d9ef;
}
.highlight .mf {
  color: #ae81ff;
}
.highlight .mh {
  color: #ae81ff;
}
.highlight .il {
  color: #ae81ff;
}
.highlight .mi {
  color: #ae81ff;
}
.highlight .mo {
  color: #ae81ff;
}
.highlight .m, .highlight .mb, .highlight .mx {
  color: #ae81ff;
}
.highlight .sc {
  color: #ae81ff;
}
.highlight .se {
  color: #ae81ff;
}
.highlight .ss {
  color: #ae81ff;
}
.highlight .sd {
  color: #e6db74;
}
.highlight .s2 {
  color: #e6db74;
}
.highlight .sb {
  color: #e6db74;
}
.highlight .sh {
  color: #e6db74;
}
.highlight .si {
  color: #e6db74;
}
.highlight .sx {
  color: #e6db74;
}
.highlight .s1 {
  color: #e6db74;
}
.highlight .s {
  color: #e6db74;
}
.highlight .na {
  color: #a6e22e;
}
.highlight .nc {
  color: #a6e22e;
}
.highlight .nd {
  color: #a6e22e;
}
.highlight .ne {
  color: #a6e22e;
}
.highlight .nf {
  color: #a6e22e;
}
.highlight .vc {
  color: #ffffff;
}
.highlight .nn {
  color: #ffffff;
}
.highlight .nl {
  color: #ffffff;
}
.highlight .ni {
  color: #ffffff;
}
.highlight .bp {
  color: #ffffff;
}
.highlight .vg {
  color: #ffffff;
}
.highlight .vi {
  color: #ffffff;
}
.highlight .nv {
  color: #ffffff;
}
.highlight .w {
  color: #ffffff;
}
.highlight {
  color: #ffffff;
}
.highlight .n, .highlight .py, .highlight .nx {
  color: #ffffff;
}
.highlight .ow {
  color: #f92672;
}
.highlight .nt {
  color: #f92672;
}
.highlight .k, .highlight .kv {
  color: #f92672;
}
.highlight .kn {
  color: #f92672;
}
.highlight .kp {
  color: #f92672;
}
.highlight .o {
  color: #f92672;
}
    </style>
      <link href="<?php echo asset_url(); ?>/web/api_documentation/stylesheets/screen.css" rel="stylesheet" media="screen" />
    <link href="<?php echo asset_url(); ?>/web/api_documentation/stylesheets/print.css" rel="stylesheet" media="print" />
      <script src="<?php echo asset_url(); ?>/web/api_documentation/javascripts/all.js"></script>
  </head>

  <body class="index" data-languages="[&quot;shell&quot;,&quot;ruby&quot;,&quot;python&quot;,&quot;javascript&quot;]">
    <a href="#" id="nav-button">
      <span>
        NAV
        <img src="<?php echo asset_url(); ?>/web/api_documentation/images/navbar.png" alt="Navbar" />
      </span>
    </a>
    <div class="toc-wrapper">
      <img src="<?php echo asset_url(); ?>/web/api_documentation/images/logo.png" class="logo" alt="Logo" />
      <div class="search">
       <input type="text" value="ButterFLi">
       </div>
        <div class="lang-selector">
              <a href="#" data-language-name="shell">shell</a>
        </div>
         <div class="search">
          <input type="text" class="search" id="input-search" placeholder="Search">
        </div>
        <ul class="search-results"></ul>
      <div id="toc" class="toc-list-h1">
          <li>
            <a href="#introduction" class="toc-h1 toc-link" data-title="Introduction">Introduction</a>
          </li>
          <li>
            <a href="#endpoints" class="toc-h1 toc-link" data-title="Endpoints">Endpoints</a>
              <ul class="toc-list-h2">
                  <li>
                    <a href="#development-sandbox" class="toc-h2 toc-link" data-title="Endpoints">Development Sandbox</a>
                  </li>
                  <li>
                    <a href="#production-use" class="toc-h2 toc-link" data-title="Endpoints">Production Use</a>
                  </li>
              </ul>
          </li>
          <li>
            <a href="#account-creation" class="toc-h1 toc-link" data-title="Account Creation">Account Creation</a>
              <ul class="toc-list-h2">
                  <li>
                    <a href="#sandbox" class="toc-h2 toc-link" data-title="Account Creation">Sandbox</a>
                  </li>
                  <li>
                    <a href="#production" class="toc-h2 toc-link" data-title="Account Creation">Production</a>
                  </li>
              </ul>
          </li>
          <li>
            <a href="#authentication" class="toc-h1 toc-link" data-title="Authentication">Authentication</a>
          </li>
          <li>
            <a href="#ride-api-primitives" class="toc-h1 toc-link" data-title="Ride API Primitives">Ride API Primitives</a>
              <ul class="toc-list-h2">
                  <li>
                    <a href="#create-ride-request" class="toc-h2 toc-link" data-title="Ride API Primitives">Create Ride Request</a>
                  </li>
                  <li>
                    <a href="#ride-request-status" class="toc-h2 toc-link" data-title="Ride API Primitives">Ride Request Status</a>
                  </li>
                  <li>
                    <a href="#cancel-ride-request" class="toc-h2 toc-link" data-title="Ride API Primitives">Cancel Ride Request</a>
                  </li>
              </ul>
          </li>
          <li>
            <a href="#errors" class="toc-h1 toc-link" data-title="Errors">Errors</a>
          </li>
      </div>
    </div>
    <div class="page-wrapper">
      <div class="dark-box"></div>
      <div class="content">
        <h1 id='introduction'>Introduction</h1>
<p>Welcome to the ButterFLi Ride API! You can use our API to access Ride API endpoints, which can book a ride, Cancel a ride or show details of rides booked by this API.</p>
<h1 id='endpoints'>Endpoints</h1><h2 id='development-sandbox'>Development Sandbox</h2>
<p><code>https://sandbox.gobutterfli.com/ride</code></p>
<h2 id='production-use'>Production Use</h2>
<p><code>https://api.gobutterfli.com/ride</code></p>

<p>You must create a different account for each endpoint, and also use the correct <code>&lt;client_id&gt; &amp; &lt;client_secret&gt;</code> credentials when authenticating for each endpoint.</p>
<h1 id='account-creation'>Account Creation</h1><h2 id='sandbox'>Sandbox</h2>
<p><a href="https://sandbox.gobutterfli.com/consumer/signup">Sign Up for account</a></p>

<p><a href="https://sandbox.gobutterfli.com/consumer/signin">Sign in and get your credentials</a></p>
<h2 id='production'>Production</h2>
<p><a href="https://api.gobutterfli.com/consumer/signup">Sign Up for account</a></p>

<p><a href="https://api.gobutterfli.com/consumer/signin">Sign in and get your credentials</a></p>

<p><strong>NOTE: You must contact us at (855) 267-2354 in order to become a ButterFLi Customer before your account will be approved.</strong></p>
<h1 id='authentication'>Authentication</h1>
<blockquote>
<p>Request</p>
</blockquote>
<pre class="highlight shell tab-shell"><code>curl -X POST
  -H <span class="s2">"Content-Type: application/x-www-form-urlencoded"</span>
  --user <span class="s2">"&lt;client_id&gt;:&lt;client_secret&gt;"</span>
  -d <span class="s1">'grant_type=client_credentials'</span>
  <span class="s1">'https://ride.gobutterfli.com/authenticate/access_token'</span>
</code></pre>
<blockquote>
<p>JSON Returned</p>
</blockquote>
<pre class="highlight json tab-json"><code><span class="p">{</span><span class="w">
  </span><span class="s2">"access_token"</span><span class="p">:</span><span class="w"> </span><span class="s2">"&lt;client_token&gt;"</span><span class="p">,</span><span class="w">
  </span><span class="s2">"token_type"</span><span class="p">:</span><span class="w"> </span><span class="s2">"Bearer"</span><span class="p">,</span><span class="w">
  </span><span class="s2">"expires_in"</span><span class="p">:</span><span class="w"> </span><span class="mi">3600</span><span class="w">
</span><span class="p">}</span><span class="w">
</span></code></pre>
<table><thead>
<tr>
<th style="text-align: left"></th>
<th style="text-align: left"></th>
</tr>
</thead><tbody>
<tr>
<td style="text-align: left">Request Type</td>
<td style="text-align: left">POST</td>
</tr>
<tr>
<td style="text-align: left">Endpoint</td>
<td style="text-align: left"><code>/authenticate/access_token</code></td>
</tr>
</tbody></table>

<table><thead>
<tr>
<th style="text-align: left">HTTP Header</th>
<th style="text-align: left">Value</th>
</tr>
</thead><tbody>
<tr>
<td style="text-align: left">Authorization</td>
<td style="text-align: left">Basic &lt;client_id&gt;:&lt;client_secret&gt;</td>
</tr>
<tr>
<td style="text-align: left"></td>
<td style="text-align: left">NOTE: client_secret must be Base 64 Encoded</td>
</tr>
</tbody></table>

<p>The Ride API uses client_id, client_secret and grant_type to generate a token which authorizes access to the API on a temporary basis. The Ride API expects the authorization token to be included in all API requests with tokken type to the server in a header that looks like the following:</p>

<p><code>Authorization: Bearer &lt;my-butterfli-token&gt;</code></p>

<p>Make the request on the right to obtain your client_token, grant type and expiration.</p>

<aside class="success">
Use the returned access_token and token_type for subsequent API calls
</aside>
<h1 id='ride-api-primitives'>Ride API Primitives</h1><h2 id='create-ride-request'>Create Ride Request</h2>
<blockquote>
<p>Request</p>
</blockquote>
<pre class="highlight shell tab-shell"><code>curl -X POST -H <span class="s2">"Content-Type: application/json"</span>
  -H <span class="s1">'Authorization: Bearer &lt;access_token&gt;'</span>
  -d <span class="s1">'{
    "consumer_email": "you@yourdomain.com",
    "ride_type": "2",
    "passenger_firstname": "test",
    "passenger_lastname": "test",
    "passenger_phone": "+00000000000",
    "passenger_email": "rider@theirdomain.com",
    "passenger_pickupaddress": "address where user is currently waiting",
    "passenger_dropoffaddress": "address where user want to go",
    "pickup_date": "MM-DD-YYYY",
    "pickup_time": "12:00:00 AM",
    "user_timezone": "",
    "services":"2"
  }'</span>
  <span class="s1">'https://ride.gobutterfli.com/ride/create_request'</span>
</code></pre>
<blockquote>
<p>JSON Returned</p>
</blockquote>
<pre class="highlight json tab-json"><code><span class="p">{</span><span class="w">
  </span><span class="s2">"success"</span><span class="p">:</span><span class="w"> </span><span class="mi">1</span><span class="w">
  </span><span class="s2">"ride_status"</span><span class="p">:</span><span class="w"> </span><span class="s2">"Yet To Start"</span><span class="w">
  </span><span class="s2">"ride_id"</span><span class="p">:</span><span class="w"> </span><span class="mi">681</span><span class="w">
  </span><span class="s2">"ride_type"</span><span class="p">:</span><span class="w"> </span><span class="s2">"wheelchair"</span><span class="w">
  </span><span class="s2">"passenger_contact_name"</span><span class="p">:</span><span class="w"> </span><span class="s2">"First"</span><span class="w">
  </span><span class="s2">"passenger_phone"</span><span class="p">:</span><span class="w"> </span><span class="s2">"+1111111111"</span><span class="w">
  </span><span class="s2">"passenger_email"</span><span class="p">:</span><span class="w"> </span><span class="s2">"you@yourdomain.com"</span><span class="w">
  </span><span class="s2">"timezone"</span><span class="p">:</span><span class="w"> </span><span class="s2">"USA/Los_Angeles"</span><span class="w">
  </span><span class="s2">"total_amount"</span><span class="p">:</span><span class="w"> </span><span class="mf">29.15</span><span class="w">
  </span><span class="s2">"pickup_address"</span><span class="p">:</span><span class="w"> </span><span class="s2">"passneger pickup location"</span><span class="w">
  </span><span class="s2">"dropoff_address"</span><span class="p">:</span><span class="w"> </span><span class="s2">"passneger dropoff location"</span><span class="w">
  </span><span class="s2">"origin_latitude"</span><span class="p">:</span><span class="w"> </span><span class="mf">28.6294093</span><span class="w">
  </span><span class="s2">"origin_longitude"</span><span class="p">:</span><span class="w"> </span><span class="mf">77.4329048</span><span class="w">
  </span><span class="s2">"request_date_time"</span><span class="p">:</span><span class="w"> </span><span class="s2">"2017-08-24 12:33:58"</span><span class="w">
  </span><span class="s2">"request_create_time"</span><span class="p">:</span><span class="w"> </span><span class="s2">"2017-10-02 17:57:06"</span><span class="w">
  </span><span class="s2">"confirmed"</span><span class="p">:</span><span class="w"> </span><span class="s2">"FALSE"</span><span class="w">
</span><span class="p">}</span><span class="w">
</span></code></pre>
<table><thead>
<tr>
<th style="text-align: left"></th>
<th style="text-align: left"></th>
</tr>
</thead><tbody>
<tr>
<td style="text-align: left">Request Type</td>
<td style="text-align: left">POST</td>
</tr>
<tr>
<td style="text-align: left">Endpoint</td>
<td style="text-align: left"><code>/ride/create_request</code></td>
</tr>
</tbody></table>
<h3 id='request-parameters'>Request Parameters</h3>
<table><thead>
<tr>
<th style="text-align: left">Parameter</th>
<th style="text-align: left">Description</th>
<th style="text-align: center">Required</th>
</tr>
</thead><tbody>
<tr>
<td style="text-align: left">consumer_email</td>
<td style="text-align: left">Consumer email address who is booking the ride.</td>
<td style="text-align: center">Y</td>
</tr>
<tr>
<td style="text-align: left">ride_type</td>
<td style="text-align: left">type of Services Ambulatory, Wheelchair, Gurney .</td>
<td style="text-align: center">Y</td>
</tr>
<tr>
<td style="text-align: left">service_type</td>
<td style="text-align: left">Service type provided by the Hospital Providers.</td>
<td style="text-align: center">Y</td>
</tr>
<tr>
<td style="text-align: left">passenger_firstname</td>
<td style="text-align: left">Name of Passenger traveling.</td>
<td style="text-align: center">Y</td>
</tr>
<tr>
<td style="text-align: left">passenger_lastname</td>
<td style="text-align: left">Last Name of Passenger traveling.</td>
<td style="text-align: center">Y</td>
</tr>
<tr>
<td style="text-align: left">passenger_phone</td>
<td style="text-align: left">Phone Number of Passenger traveling.</td>
<td style="text-align: center">Y</td>
</tr>
<tr>
<td style="text-align: left">passenger_email</td>
<td style="text-align: left">Email address of Passenger traveling.</td>
<td style="text-align: center">Y</td>
</tr>
<tr>
<td style="text-align: left">passenger_pickupaddress</td>
<td style="text-align: left">Pickup address of Passenger traveling.</td>
<td style="text-align: center">Y</td>
</tr>
<tr>
<td style="text-align: left">passenger_dropoffaddress</td>
<td style="text-align: left">Dropoff address of Passenger traveling.</td>
<td style="text-align: center">Y</td>
</tr>
<tr>
<td style="text-align: left">pickup_latitude</td>
<td style="text-align: left">Latitude of pickup location</td>
<td style="text-align: center">Y</td>
</tr>
<tr>
<td style="text-align: left">pickup_longitude</td>
<td style="text-align: left">Longitude of pickup location</td>
<td style="text-align: center">Y</td>
</tr>
<tr>
<td style="text-align: left">dropoff_latitude</td>
<td style="text-align: left">Latitude of drop off location</td>
<td style="text-align: center">Y</td>
</tr>
<tr>
<td style="text-align: left">dropoff_longitude</td>
<td style="text-align: left">Longitude of drop off location</td>
<td style="text-align: center">Y</td>
</tr>
<tr>
<td style="text-align: left">pickup_date</td>
<td style="text-align: left">Pickup date of Passenger traveling.</td>
<td style="text-align: center">Y</td>
</tr>
<tr>
<td style="text-align: left">pickup_time</td>
<td style="text-align: left">Pickup time of Passenger traveling.</td>
<td style="text-align: center">Y</td>
</tr>
<tr>
<td style="text-align: left">user_timezone</td>
<td style="text-align: left">Time zone of Passenger traveling.</td>
<td style="text-align: center">Y</td>
</tr>
</tbody></table>

<aside class="success">
Your ride request was booked; call the <code>/ride/ride_details</code> endpoint to see your ride progress to dispatched, in progress, completed, and possibly cancelled.
</aside>
<h2 id='ride-request-status'>Ride Request Status</h2>
<blockquote>
<p>Request</p>
</blockquote>
<pre class="highlight shell tab-shell"><code>curl -X POST
  -H <span class="s2">"Content-Type: application/json"</span>
  -H <span class="s1">'Authorization: Bearer &lt;access_token&gt;'</span>
  -d <span class="s1">'{
    "consumer_email": "you@yourdomain.com",
    "request_id": "&lt;ride_id&gt;"
  }'</span>
  <span class="s1">'https://ride.gobutterfli.com/ride/ride_details'</span>
</code></pre>
<blockquote>
<p>JSON Returned</p>
</blockquote>
<pre class="highlight json tab-json"><code><span class="p">{</span><span class="w">
  </span><span class="s2">"success"</span><span class="p">:</span><span class="w"> </span><span class="mi">1</span><span class="p">,</span><span class="w">
  </span><span class="s2">"ride_status"</span><span class="p">:</span><span class="w"> </span><span class="s2">"Cancelled"</span><span class="p">,</span><span class="w">
  </span><span class="s2">"ride_id"</span><span class="p">:</span><span class="w"> </span><span class="mi">681</span><span class="p">,</span><span class="w">
  </span><span class="s2">"ride_type"</span><span class="p">:</span><span class="w"> </span><span class="s2">"wheelchair"</span><span class="w">
  </span><span class="s2">"passenger_contact_name"</span><span class="p">:</span><span class="w"> </span><span class="s2">"First"</span><span class="w">
  </span><span class="s2">"passenger_phone"</span><span class="p">:</span><span class="w"> </span><span class="s2">"+1111111111"</span><span class="w">
  </span><span class="s2">"passenger_email"</span><span class="p">:</span><span class="w"> </span><span class="s2">"rider@theirdomain.com"</span><span class="w">
  </span><span class="s2">"driver_contact_name"</span><span class="p">:</span><span class="w"> </span><span class="kc">null</span><span class="p">,</span><span class="w">
  </span><span class="s2">"driver_phone"</span><span class="p">:</span><span class="w"> </span><span class="kc">null</span><span class="p">,</span><span class="w">
  </span><span class="s2">"driver_email"</span><span class="p">:</span><span class="w"> </span><span class="kc">null</span><span class="p">,</span><span class="w">
  </span><span class="s2">"timezone"</span><span class="p">:</span><span class="w"> </span><span class="s2">"USA/Los_Angeles"</span><span class="p">,</span><span class="w">
  </span><span class="s2">"estimated_time"</span><span class="p">:</span><span class="w"> </span><span class="s2">"12 minutes"</span><span class="p">,</span><span class="w">
  </span><span class="s2">"total_amount"</span><span class="p">:</span><span class="w"> </span><span class="mf">194.68</span><span class="p">,</span><span class="w">
  </span><span class="s2">"pickup_address"</span><span class="p">:</span><span class="w"> </span><span class="s2">"address where driver need to pick user"</span><span class="p">,</span><span class="w">
  </span><span class="s2">"dropoff_address"</span><span class="p">:</span><span class="w"> </span><span class="s2">"address where driver need to drop user"</span><span class="p">,</span><span class="w">
  </span><span class="s2">"origin_latitude"</span><span class="p">:</span><span class="w"> </span><span class="mf">28.6294093</span><span class="w">
  </span><span class="s2">"origin_longitude"</span><span class="p">:</span><span class="w"> </span><span class="mf">77.4329048</span><span class="w">
  </span><span class="s2">"dest_latitude"</span><span class="p">:</span><span class="w"> </span><span class="mf">28.6294093</span><span class="w">
  </span><span class="s2">"dest_longitude"</span><span class="p">:</span><span class="w"> </span><span class="mf">77.4329048</span><span class="w">
  </span><span class="s2">"request_date_time"</span><span class="p">:</span><span class="w"> </span><span class="s2">"2017-08-24 12:33:58"</span><span class="p">,</span><span class="w">
  </span><span class="s2">"request_create_time"</span><span class="p">:</span><span class="w"> </span><span class="s2">"2017-10-02 17:57:06"</span><span class="p">,</span><span class="w">
  </span><span class="s2">"cancelled_by"</span><span class="p">:</span><span class="w"> </span><span class="s2">"rider@theirdomain.com"</span><span class="w">
</span><span class="p">}</span><span class="w">
</span></code></pre>
<p>Request the status of your ride request. Use this call to track ride progress and completion.</p>

<table><thead>
<tr>
<th style="text-align: left"></th>
<th style="text-align: left"></th>
</tr>
</thead><tbody>
<tr>
<td style="text-align: left">Request Type</td>
<td style="text-align: left">POST</td>
</tr>
<tr>
<td style="text-align: left">Endpoint</td>
<td style="text-align: left"><code>/ride/ride_details</code></td>
</tr>
</tbody></table>
<h3 id='request-parameters-2'>Request Parameters</h3>
<table><thead>
<tr>
<th style="text-align: left">Parameter</th>
<th style="text-align: left">Description</th>
<th style="text-align: center">Required</th>
</tr>
</thead><tbody>
<tr>
<td style="text-align: left">request_id</td>
<td style="text-align: left">ride_id as returned from /ride/create_request call</td>
<td style="text-align: center">Y</td>
</tr>
<tr>
<td style="text-align: left">consumer_email</td>
<td style="text-align: left">consumer_email as returned from /ride/create_request call</td>
<td style="text-align: center">Y</td>
</tr>
</tbody></table>

<aside class="success">
Ride details are returned
</aside>
<h2 id='cancel-ride-request'>Cancel Ride Request</h2>
<blockquote>
<p>Request</p>
</blockquote>
<pre class="highlight shell tab-shell"><code>curl -X POST -H <span class="s2">"Content-Type: application/json"</span>
  -H <span class="s1">'Authorization: Bearer &lt;access_token&gt;'</span>
  -d <span class="s1">'{
    "request_id": "identification",
    "cancel_reason": "Reasons",
    "consumer_email": "you@yourdomain.com"
  }'</span>
  <span class="s1">'https://ride.gobutterfli.com/ride/cancel_ride'</span>
</code></pre>
<blockquote>
<p>JSON Returned</p>
</blockquote>
<pre class="highlight json tab-json"><code><span class="p">{</span><span class="w">
  </span><span class="s2">"success"</span><span class="p">:</span><span class="w"> </span><span class="mi">1</span><span class="w">
</span><span class="p">}</span><span class="w">
</span></code></pre>
<p>Use this call to cancel a ride. If a ride is in the booked state, you will not be charged. However, once a driver has been dispatched, the cancellation charge is equal to the base fare for your account.</p>

<table><thead>
<tr>
<th style="text-align: left"></th>
<th style="text-align: left"></th>
</tr>
</thead><tbody>
<tr>
<td style="text-align: left">Request Type</td>
<td style="text-align: left">POST</td>
</tr>
<tr>
<td style="text-align: left">Endpoint</td>
<td style="text-align: left"><code>/ride/cancel_ride</code></td>
</tr>
</tbody></table>
<h3 id='request-parameters-3'>Request Parameters</h3>
<table><thead>
<tr>
<th style="text-align: left">Parameter</th>
<th style="text-align: left">Description</th>
<th style="text-align: center">Required</th>
</tr>
</thead><tbody>
<tr>
<td style="text-align: left">request_id</td>
<td style="text-align: left">ride_id as returned from /ride/create_request call</td>
<td style="text-align: center">Y</td>
</tr>
<tr>
<td style="text-align: left">consumer_email</td>
<td style="text-align: left">consumer_email as returned from /ride/create_request call</td>
<td style="text-align: center">Y</td>
</tr>
<tr>
<td style="text-align: left">cancel_reason</td>
<td style="text-align: left">text explanation of cancellation</td>
<td style="text-align: center">Y</td>
</tr>
</tbody></table>
<h1 id='errors'>Errors</h1>
<p>The ButterFLi Ride API uses the following error codes:</p>

<table><thead>
<tr>
<th>Error Code</th>
<th>Meaning</th>
</tr>
</thead><tbody>
<tr>
<td>400</td>
<td>Bad Request -- Your request was malformed.</td>
</tr>
<tr>
<td>401</td>
<td>Unauthorized -- Error: access denied. The resource owner or authorization server denied the request.</td>
</tr>
<tr>
<td>403</td>
<td>Forbidden -- Your credentials have been revoked or are not authorized</td>
</tr>
<tr>
<td>404</td>
<td>Not Found -- Object not found</td>
</tr>
<tr>
<td>405</td>
<td>Method Not Allowed -- You tried to access the API with an invalid method.</td>
</tr>
<tr>
<td>500</td>
<td>Internal Server Error -- We had a problem with our server. Try again later.</td>
</tr>
<tr>
<td>502</td>
<td>Bad Gateway -- We&#39;re temporarily offline for maintenance. Please try again later.</td>
</tr>
<tr>
<td>503</td>
<td>Service Unavailable -- We&#39;re temporarily offline for maintenance. Please try again later.</td>
</tr>
</tbody></table>

      </div>
      <div class="dark-box">
          <div class="lang-selector">
                <a href="#" data-language-name="shell">shell</a>
          </div>
      </div>
    </div>
  </body>
</html>
