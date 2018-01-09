<?php

declare(strict_types=1);

include_once __DIR__ . '/stubs/GlobalStubs.php';
include_once __DIR__ . '/stubs/KernelStubs.php';
include_once __DIR__ . '/stubs/ModuleStubs.php';

use PHPUnit\Framework\TestCase;

class SyncTest extends TestCase
{
    private $assistantModuleID = '{BB6EF5EE-1437-4C80-A16D-DA0A6C885210}';
    private $agentUserId = '';

    public function setUp()
    {
        //Licensee is used as agentUserId
        $this->agentUserId = md5(IPS_GetLicensee());

        //Reset
        IPS\Kernel::reset();

        //Register our library we need for testing
        IPS\ModuleLoader::loadLibrary(__DIR__ . '/../library.json');

        parent::setUp();
    }

    public function testCreate()
    {
        IPS_CreateInstance($this->assistantModuleID);
        $this->assertEquals(count(IPS_GetInstanceListByModuleID($this->assistantModuleID)), 1);
    }

    public function testEmptySync()
    {
        $iid = IPS_CreateInstance($this->assistantModuleID);
        $intf = IPS\InstanceManager::getInstanceInterface($iid);
        $this->assertTrue($intf instanceof Assistant);

        $testRequest = <<<'EOT'
{
    "requestId": "ff36a3cc-ec34-11e6-b1a0-64510650abcf",
    "inputs": [{
        "intent": "action.devices.SYNC"
    }]
}            
EOT;

        $testResponse = <<<EOT
{
    "requestId": "ff36a3cc-ec34-11e6-b1a0-64510650abcf",
    "payload": {
        "agentUserId": "$this->agentUserId",
        "devices": []
    }
}
EOT;

        $this->assertEquals($intf->SimulateData(json_decode($testRequest, true)), json_decode($testResponse, true));
    }

    public function testLightSwitchSync()
    {
        $vid = IPS_CreateVariable(0 /* Boolean */);

        $iid = IPS_CreateInstance($this->assistantModuleID);

        IPS_SetConfiguration($iid, json_encode([
            'DeviceLightSwitch' => json_encode([
                [
                    'ID'      => '1',
                    'Name'    => 'Flur Licht',
                    'OnOffID' => $vid
                ]
            ])
        ]));
        IPS_ApplyChanges($iid);

        $intf = IPS\InstanceManager::getInstanceInterface($iid);
        $this->assertTrue($intf instanceof Assistant);

        $testRequest = <<<'EOT'
{
    "requestId": "ff36a3cc-ec34-11e6-b1a0-64510650abcf",
    "inputs": [{
        "intent": "action.devices.SYNC"
    }]
}            
EOT;

        $testResponse = <<<EOT
{
    "requestId": "ff36a3cc-ec34-11e6-b1a0-64510650abcf",
    "payload": {
        "agentUserId": "$this->agentUserId",
        "devices": [
            {
                  "id": "1",
                  "type": "action.devices.types.LIGHT",
                  "traits": [
                    "action.devices.traits.OnOff"
                  ],
                  "name": {
                      "name": "Flur Licht"
                  },
                  "willReportState": false
            }
        ]
    }
}
EOT;

        $this->assertEquals($intf->SimulateData(json_decode($testRequest, true)), json_decode($testResponse, true));
    }

    public function testLightDimmerSync()
    {
        $vid = IPS_CreateVariable(1 /* Integer */);

        $iid = IPS_CreateInstance($this->assistantModuleID);

        IPS_SetConfiguration($iid, json_encode([
            'DeviceLightDimmer' => json_encode([
                [
                    'ID'                => '1',
                    'Name'              => 'Flur Licht',
                    'BrightnessOnOffID' => $vid
                ]
            ])
        ]));
        IPS_ApplyChanges($iid);

        $intf = IPS\InstanceManager::getInstanceInterface($iid);
        $this->assertTrue($intf instanceof Assistant);

        $testRequest = <<<'EOT'
{
    "requestId": "ff36a3cc-ec34-11e6-b1a0-64510650abcf",
    "inputs": [{
        "intent": "action.devices.SYNC"
    }]
}            
EOT;

        $testResponse = <<<EOT
{
    "requestId": "ff36a3cc-ec34-11e6-b1a0-64510650abcf",
    "payload": {
        "agentUserId": "$this->agentUserId",
        "devices": [
            {
                  "id": "1",
                  "type": "action.devices.types.LIGHT",
                  "traits": [
                    "action.devices.traits.Brightness",
                    "action.devices.traits.OnOff"
                  ],
                  "name": {
                      "name": "Flur Licht"
                  },
                  "willReportState": false
            }
        ]
    }
}
EOT;

        $this->assertEquals($intf->SimulateData(json_decode($testRequest, true)), json_decode($testResponse, true));
    }

    public function testLightColorSync()
    {
        $vid = IPS_CreateVariable(1 /* Integer */);

        $iid = IPS_CreateInstance($this->assistantModuleID);

        IPS_SetConfiguration($iid, json_encode([
            'DeviceLightColor' => json_encode([
                [
                    'ID'                   => '123',
                    'Name'                 => 'Flur Licht',
                    'ColorSpectrumOnOffID' => $vid
                ]
            ])
        ]));
        IPS_ApplyChanges($iid);

        $intf = IPS\InstanceManager::getInstanceInterface($iid);
        $this->assertTrue($intf instanceof Assistant);

        $testRequest = <<<'EOT'
{
    "requestId": "ff36a3cc-ec34-11e6-b1a0-64510650abcf",
    "inputs": [{
        "intent": "action.devices.SYNC"
    }]
}
EOT;

        $testResponse = <<<EOT
{
    "requestId": "ff36a3cc-ec34-11e6-b1a0-64510650abcf",
    "payload": {
        "agentUserId": "$this->agentUserId",
        "devices": [
            {
                "id": "123",
                "type": "action.devices.types.LIGHT",
                "traits": [
                    "action.devices.traits.ColorSpectrum",
                    "action.devices.traits.OnOff"
                ],
                "name": {
                    "name": "Flur Licht"
                },
                "willReportState": false,
                "attributes": {
                    "colorModel": "rgb"
                }
            }
        ]
    }
}
EOT;

        $this->assertEquals($intf->SimulateData(json_decode($testRequest, true)), json_decode($testResponse, true));
    }
}
