<?php

//
// Date: 2018-11-24
// Author: Spicer Matthews (spicer@cloudmanic.com)
// Last Modified by: spicer
// Last Modified: 2018-11-25
// Copyright: 2017 Cloudmanic Labs, LLC. All rights reserved.
//

require 'vendor/autoload.php';

// Digital Ocean Access token
$doAccessToken = "XXXXXXXXXXXXX";

// Get a list of Linode domains we are importing
$linodeDomains = json_decode(`linode-cli domains list --json`, true);

// Set the Guzzle client for talking to the DO API.
$client = new GuzzleHttp\Client(['base_uri' => 'https://api.digitalocean.com/v2/']);

// Set the headers for DO API. Mainly the access token goes here.
$headers = [
  'Authorization' => 'Bearer ' . $doAccessToken,        
  'Accept'        => 'application/json',
];


// Loop through the Linode Domains and send them over to DO.
foreach($linodeDomains AS $key => $row) 
{
  doLinodeDomain($client, $headers, $row);
}


//
// Process a Linode domain.
// 
function doLinodeDomain($client, $headers, $domain)
{
  echo 'Processing ' . $domain['domain'] . "\n";

  // Get the records from Linode for this domain
  $records = json_decode(`linode-cli domains records-list $domain[id] --json`, true);

  // Create domain at DO
  createDomain($client, $headers, $domain['domain']);

  // Loop through and create records
  foreach($records AS $key => $row)
  {
    addRecord($client, $headers, $domain['domain'], $row['type'], $row['name'], $row['target'], $row['ttl_sec'], $row['priority'], $row['weight']);
  }

}

//
// Create new domain
//
function createDomain($client, $headers, $domain)
{
  $response = $client->request('POST', 'domains', [ 
    'headers' => $headers, 
    'json' => [ 'name' => $domain ]
  ]);

  echo "$domain has been created - " . $response->getBody() . "\n";
}

//
// Add record
//
function addRecord($client, $headers, $domain, $type, $name, $data, $ttl, $priority, $weight)
{
  echo "Processing $type record for $domain...\n";

  // If TTL is zero it is the Linode default. So we just hardcode to 5 mins.
  if($ttl == 0)
  {
    $ttl = 360;
  }

  // Some data needs to end with a dot.
  if(($type == "MX") || ($type == "CNAME"))
  {
    $data = $data . "."; 
  }

  // Put "@" in for empty host names
  if($name == "")
  {
    $name = "@";
  }

  $response = $client->request('POST', 'domains/' . $domain . '/records', [ 
    'headers' => $headers, 
    'json' => [ 
      "type" => $type,
      "name" => $name,
      "data" => $data,
      "priority" => $priority,
      // "port": null,
      "ttl" => $ttl,
      "weight" => $weight,
      // "flags": null,
      // "tag": null
    ]
  ]);

  echo "$type ($name) record added to $domain - " . $response->getBody() . "\n"; 
}

/* End File */