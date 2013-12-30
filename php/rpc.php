<?php

require_once('xmlrpc.php');

function prepareKey($k)
{
	if (substr($k, -1) !== "=")
	{
		$k = $k . "=";
	}
	if (substr($k, 1, 1) !== ".")
	{
		$k = "d." . $k;
	}
	return $k;
}

header('Content-type: application/json');

if (isset($_REQUEST["action"]))
{
	if ($_REQUEST["action"] === "list")
	{
		if(isset($_REQUEST["args"]))
		{
			$reqKeys = array();
			$keys = explode(",", $_REQUEST["args"]);
			if (is_array($keys) && count($keys) && strlen($keys[0]))
			{
				for ($i = 0; $i < count($keys); $i++)
				{
					$reqKeys[$i] = prepareKey($keys[$i]);
				}
				$cmd = new rXMLRPCCommand("d.multicall");
				$cmd->addParameter("main");
				$cmd->addParameters($reqKeys);
				$req = new rXMLRPCRequest($cmd);
				if($req->success())
				{
					if (!(count($req->val) % count($reqKeys)))
					{
						$dict = array();
						$externiter = 0;
						$interniter = 0;
						for($i = 0; $i < count($req->val); $i++)
						{
							if ($interniter === 0)
								$dict[$externiter] = array();

							$dict[$externiter][$keys[$interniter]] = $req->val[$i];
							if (++$interniter >= count($keys))
							{
								$interniter = 0;
								++$externiter;
							}
						}
						echo json_encode(array("success" => true, "torrents" => $dict));
					}
					else
					{
						echo json_encode(array("success" => false, "error" => "The XML response had an amount of objects that was not a multiple of the amount in the request; " . count($req->val) . " instead of " . count($reqKeys) . ". Check that the keys you specified are correct."));
					}
				}
				else
				{
					echo json_encode(array("success" => false, "error" => "The XML request was unsuccessful"));
				}
			}
			else
			{
				echo json_encode(array("success" => false, "error" => "No parameters were passed although a parameter call was specified"));
			}
		}
	}
	else
	{
		if (isset($_REQUEST["hashes"]))
		{
			if (substr($_REQUEST["action"], 1, 1) === ".")
			{
				$cmdstr = $_REQUEST["action"];
			}
			else
			{
				$cmdstr = "d." . $_REQUEST["action"];
			}
			$hashes = explode(",", $_REQUEST["hashes"]);
			if (is_array($hashes) && count($hashes) && strlen($hashes[0]))
			{
				$success = true;
				for ($i = 0; $i < count($hashes); $i++)
				{
					$cmd = new rXMLRPCCommand($cmdstr);
					$cmd->addParameter($hashes[$i]);
					$req = new rXMLRPCRequest($cmd);
					if(!$req->success())
					{
						$success = false;
					}
				}
				if ($success)
				{
					echo json_encode(array("success" => true));
				}
				else
				{
					echo json_encode(array("success" => false, "error" => "The XML request was unsuccessful"));
				}
			}
			else
			{
				echo json_encode(array("success" => false, "error" => "No hashes were passed although a parameter call was specified"));
			}
		}
		else
		{
			echo json_encode(array("success" => false, "error" => "At least one torrent hash is required for action calls"));
		}
	}
}
else
{
	echo json_encode(array("success" => false, "error" => "An action was not specified"));
}
