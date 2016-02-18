<?php
/* HTTP-POST check  */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  define('ABSPATH', dirname(__FILE__));
  include ABSPATH . '/webgl-conformance-tests.config.php';

  if (defined('WGLTS_DEBUG') && WGLTS_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', ABSPATH . '/debug.log');
  } else {
    error_reporting(E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR);
    ini_set('display_errors', 0);
  }

  /* AJAX check  */
  if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    $content = file_get_contents('php://input');
    
    if (defined('WGLTS_DEBUG') && WGLTS_DEBUG) {
      $json = json_decode($content, true);
      error_log(print_r($json, true));
    }
    
    if (defined('WGLTS_REPORT_URL') && WGLTS_REPORT_URL) {
      $postdata = http_build_query(array(
        'jsonContent' => $content,
      ), '', '&');
      $ch = curl_init(WGLTS_REPORT_URL);
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

      error_log('Sending report to ' . WGLTS_REPORT_URL . '...');
      $response = curl_exec($ch);
      $info = curl_getinfo($ch);
      curl_close($ch);
      if ($response === false) {
        error_log('Send failed: ' . PHP_EOL . print_r($info, true));
      } else {
        error_log('Send succeeded: ' . $response . PHP_EOL . print_r($info, true));
      }
    } else {
      $response = '0';
    }
    
    /* special ajax here */
    header('Content-type: text/plain', true, 200);
    die($response);
  }
  
  header('Content-type: text/plain', true, 400);
  die('Bad request');
}
?><!--

/*
** Copyright (c) 2013 The Khronos Group Inc.
**
** Permission is hereby granted, free of charge, to any person obtaining a
** copy of this software and/or associated documentation files (the
** "Materials"), to deal in the Materials without restriction, including
** without limitation the rights to use, copy, modify, merge, publish,
** distribute, sublicense, and/or sell copies of the Materials, and to
** permit persons to whom the Materials are furnished to do so, subject to
** the following conditions:
**
** The above copyright notice and this permission notice shall be included
** in all copies or substantial portions of the Materials.
**
** THE MATERIALS ARE PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
** EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
** MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
** IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
** CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
** TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
** MATERIALS OR THE USE OR OTHER DEALINGS IN THE MATERIALS.
*/

-->
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<!-- Prevents Chrome from offering to translate tests which generate
     random characters for things like attribute names -->
<meta name="google" value="notranslate">
<title>WebGL Conformance Tests</title>
<style>
  body {
    background: #fff;
    color: #000;
  }
  
  body {
    border: 0;
    margin: 0;
    padding: 0;
    height: 100%;
    max-height:100%;
    font-family: Verdana, Arial, sans-serif;
    font-size: 0.8em;
  }

  a {
    color: #88F;
    text-decoration: none;
  }

  a:hover {
    border-bottom: 1px solid #66D;
  }

  #testlist {
    position:fixed;
    top:310px;
    left:0;
    right:0;
    bottom:0px;
    overflow:auto;
    padding:1em;
  }

  #header {
    position:absolute;
    top:0;
    left:0;
    width:100%;
    height:310px;
    overflow:auto;
    border-bottom: 1px solid #CCC;
  }

  #info {
    text-align: center;
    min-width: 300px;
  }

  table {
    width: 100%;
    height: 100%;
    border: 0;
  }

  #frames {
    border-left: 1px solid #CCC;
  }

  #frames td {
      min-height: 1px;
      min-width: 1px;
  }

  #frames iframe {
    border: 0;
  }
  
  #testList {
    padding:1em;
  }

  .folder {
    margin-bottom: 1.5em;
  }

  .folderHeader {
    white-space: nowrap;
  }

  .folderName {
    font-weight: bold;
  }

  .folderMessage {
    margin-left: 1em;
    font-size: 0.9em;
  }

  .pageHeader {
    white-space: nowrap;
  }

  .testpage { 
    border-style: solid;
    border-color: #CCC;
    border-width: 0px 0 1px 0;
    background-color: #FFF;
    padding: 4px 0 4px 0;

    -webkit-transition: background-color 0.25s;
    -moz-transition: background-color 0.25s;
    transition: background-color 0.25s;
  }

  .testpage:first-child { 
    border-width: 1px 0 1px 0;
  }

  .timeout { }
  .success { }
  .fail { }
  .testpage.testpagesuccess { background-color: #8F8; }
  .testpage.testpagefail { background-color: #F88; }
  .testpage.testpageskipped { background-color: #888; }
  .testpage.testpagetimeout { background-color: #FC8; }
  .nowebgl { font-weight: bold; color: red; }
  #error-wrap {
      float: left;
      position: relative;
      left: 50%;
  }
  #error {
     color: red;
     float: left;
     position: relative;
     left: -50%;
     text-align: left;
  }
  ul {
    list-style: none;
    padding-left: 1em;
  }
  
  .testpage {
    background-color: #DDD;
  }
  .testpage-torun {
    background-color: #FFF;
  }
  .testpage-toskip { 
    background-color: #AAA; 
  }
  
  .miniUI ul {
    padding: 0;
    margin: 0;
  }
  .miniUI .folder {
    margin: 0;
  }
  .miniUI .folder.hasPages {
    /*float: left;*/
    margin-right: 1em;
    margin-bottom: 1em;
  }
  .miniUI .folder .folderHeader {
    display: none;
  }
  .miniUI .folder.hasPages .folderHeader {
    display: block;
  }
  .miniUI .testpage { 
    float: left;
    border-width: 1px;
    padding: 0.7em;
    margin-bottom: 1em;
    
    -webkit-transition: none;
    -moz-transition: none;
    transition: none;
  }
  .miniUI .testpage:last-child { 
    margin-right: 1em;
  }
</style>
<script type="application/javascript" src="js/webgl-test-harness.js"></script>
<script>
"use strict";
var DEFAULT_CONFORMANCE_TEST_VERSION = "1.0.4 (beta)";

var OPTIONS = {
  version: DEFAULT_CONFORMANCE_TEST_VERSION,
  frames: 1,
  allowSkip: 0,
  root: null,
  quiet: 0
};

var testVersions = [
  "1.0.4 (beta)",
  "2.0.0 (beta)"
];

function start() {

  function log(msg) {
    if (window.console && window.console.log) {
      window.console.log(msg);
    }
  }

  function createStylesheet() {
    var style = document.createElement("style");
    style.appendChild(document.createTextNode(""));
    document.head.appendChild(style);
    return style.sheet;
  }

  function create3DContext(canvas, attrs, version) {
      if (!canvas) {
        canvas = document.createElement("canvas");
      }
      var context = null;
      var names;
      switch (version) {
        case 2:
          names = ["webgl2", "experimental-webgl2"];
          break;
        default:
          names = ["webgl", "experimental-webgl"];
          break;
      }
      for (var i = 0; i < names.length; ++i) {
        try {
          context = canvas.getContext(names[i], attrs);
        } catch (e) {
        }
        if (context) {
          break;
        }
      }
      return context;
    }
    
    function sendReport(report_type, report_data, opt_async, opt_url) {
      if (OPTIONS.sendReport == undefined || OPTIONS.sendReport == 0) {
        return;
      }
      
      var xhr = new XMLHttpRequest();
      if (opt_async === undefined) opt_async = true;
      if (opt_url === undefined) opt_url = window.location.href;
      //log('sendReport to ' + opt_url);
      xhr.open('POST', opt_url, opt_async);
      xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
      xhr.setRequestHeader('Content-Type', 'application/json;charset=UTF-8');
      var report = {
        report: report_type,
        suiteID: suiteID,
        runID: sendReport.prototype.runID,
        timestamp: Math.floor(timer.getMillis()),
        data: report_data || {}
      };
      xhr.send(JSON.stringify(report));
    }
    sendReport.prototype.startRun = function() {
      sendReport.prototype.runID = Date.now() & 0x7FFFFFFF;
    };
    sendReport.prototype.startRun();

    var Timer = function () {
      this.startT = 0;
    };
    
    Timer.prototype.now = (function () {
      var performance = window.performance || {};
      
      performance.now = performance.now || 
                        performance.mozNow || 
                        performance.msNow || 
                        performance.oNow || 
                        performance.webkitNow;
      
      if (performance.now !== undefined) {
        return function () {
          return performance.now();
        };
      }
      
      return function() {
        return Date.now();
      };
    })();
    
    Timer.prototype.start = function () {
      this.startT = this.now();
    };
    
    Timer.prototype.getStartTime = function () {
      return this.startT;
    };
    
    Timer.prototype.getMillis = function () {
      var stopT = this.now();
      if (this.startT === 0) {
        this.startT = stopT;
      }
      return stopT - this.startT;
    };
    
    var reportType = WebGLTestHarnessModule.TestHarness.reportType;
    var pageCount = 0;
    var folderCount = 0;
    var autoScrollEnabled = true; // Whether the user prefers to auto scroll
    var autoScroll = true; // Whether auto scroll is actually performed
    
    var DefaultUI = function (localDoc) {
      this.localDoc = localDoc;
      this.pages = {};
      this.folders = {};
      this.reporterUI = {};
    };
    
    DefaultUI.prototype.createElement = function (tag) {
      return this.localDoc.createElement(tag);
    };
    
    DefaultUI.prototype.createTextNode = function (data) {
      return this.localDoc.createTextNode(data);
    };
    
    DefaultUI.prototype.initializePage = function (pageObj) {
      var url = pageObj.url;
      var li = this.createElement('li');
      li.id = pageObj.elementId;
      var div = this.createElement('div');
      div.classList.add('pageHeader');
      var check = this.createElement('input');
      check.type = 'checkbox';
      check.checked = true;
      div.appendChild(check);
      var button = this.createElement('input');
      button.type = 'button';
      button.value = 'run';
      button.onclick = function () {
        autoScroll = false;
        pageObj.reporter.runTest(url);
      };
      if (pageObj.reporter.noSelectedWebGLVersion) {
        button.disabled = true;
      }
      div.appendChild(button);
      var a = this.createElement('a');
      a.href = WebGLTestHarnessModule.getURLWithOptions(url, {
        webglVersion: pageObj.reporter.selectedWebGLVersion,
        quiet: OPTIONS.quiet
      });
      a.target = "_blank";
      var node = this.createTextNode(url);
      a.appendChild(node);
      div.appendChild(a);
      li.setAttribute('class', 'testpage');
      li.appendChild(div);
      var ul = this.createElement('ul');
      var node = this.createTextNode('');
      li.appendChild(ul);
      div.appendChild(node);
      
      this.pages[pageObj.elementId] = {
        totalsElem: node,
        resultElem: ul,
        elem: li,
        check: check
      };
    };
    
    DefaultUI.prototype.addResult = function (pageObj, msg, success, skipped) {
      if (success === undefined) {
        var result = "timeout";
        var css = "timeout";
      } else if (success) {
        // don't report success.
        return;
      } else {
        var result = "failed";
        var css = "fail";
      }
      
      var node = this.createTextNode(result + ': ' + msg);
      var li = this.createElement('li');
      li.appendChild(node);
      li.setAttribute('class', css);
      this.pages[pageObj.elementId].resultElem.appendChild(li);
    };

    DefaultUI.prototype.pageChecked = function (pageObj) {
      return this.pages[pageObj.elementId].check.checked;
    };

    DefaultUI.prototype.setChecked = function (pageObj, value) {
      this.pages[pageObj.elementId].check.checked = value;
    };

    DefaultUI.prototype.startPage = function (pageObj, shouldRun) {
      var ui = this.pages[pageObj.elementId];
      if (autoScroll && ui.elem.scrollIntoView) {
        ui.elem.scrollIntoView(false);
      }
      // remove previous results.
      while (ui.resultElem.hasChildNodes()) {
        ui.resultElem.removeChild(ui.resultElem.childNodes[0]);
      }
      ui.totalsElem.textContent = '';

      if (shouldRun) {
        ui.elem.classList.remove('testpagetimeout');
        ui.elem.classList.remove('testpageskipped');
        ui.elem.classList.remove('testpagefail');
        ui.elem.classList.remove('testpagesuccess');
      }
    };
    
    DefaultUI.prototype.finishPage = function (pageObj, success) {
      var ui = this.pages[pageObj.elementId];
      
      if (pageObj.totalSkipped) {
        var msg = ' (' + pageObj.totalSkipped + ' of ' + pageObj.totalTests + ' skipped in ' + pageObj.totalTime.toFixed(1) + ' ms )';
      } else {
        var msg = ' (' + pageObj.totalSuccessful + ' of ' + pageObj.totalTests + ' passed in ' + pageObj.totalTime.toFixed(1) + ' ms )';
      }

      if (success === undefined) {
        var css = 'testpagetimeout';
        msg = '(*timeout*)';
      } else if (pageObj.totalSkipped) {
        var css = 'testpageskipped';
      } else if (pageObj.totalSuccessful != pageObj.totalTests) {
        var css = 'testpagefail';
      } else {
        var css = 'testpagesuccess';
      }
      
      ui.elem.classList.add(css);
      ui.totalsElem.textContent = msg;
    };

    DefaultUI.prototype.initializeFolder = function (folderObj) {
      var li = this.createElement('li');
      li.id = folderObj.elementId;
      li.classList.add("folder");
      
      var div = this.createElement('div');
      div.classList.add('folderHeader');
      
      var check = this.createElement('input');
      check.type = 'checkbox';
      check.checked = true;
      div.appendChild(check);
      
      var button = this.createElement('input');
      button.type = 'button';
      button.value = 'run';
      button.onclick = function () {
        autoScroll = autoScrollEnabled;
        folderObj.run();
      };
      if (folderObj.reporter.noSelectedWebGLVersion) {
        button.disabled = true;
      }
      div.appendChild(button);
      
      var h = this.createElement('span');
      h.classList.add('folderName');
      h.appendChild(this.createTextNode(folderObj.displayName));
      div.appendChild(h);
      
      var m = this.createElement('span');
      m.classList.add('folderMessage');
      var msgNode = this.createTextNode('');
      m.appendChild(msgNode);
      div.appendChild(m);
      
      var ul = this.createElement('ul');
      li.appendChild(div);
      li.appendChild(ul);
      
      this.folders[folderObj.elementId] = {
        childUL: ul,
        elem: li,
        check: check,
        folderHeader: div,
        msgNode: msgNode
      };
    };
    
    DefaultUI.prototype.folderChecked = function (folderObj) {
      return this.folders[folderObj.elementId].check.checked;
    };

    DefaultUI.prototype.setFolderChecked = function (folderObj, value) {
      this.folders[folderObj.elementId].check.checked = value;
    };
    
    DefaultUI.prototype.runFolder = function (folderObj) {
      this.folders[folderObj.elementId].msgNode.textContent = '';
    };

    DefaultUI.prototype.folderPageFinished = function (folderObj, page, success) {
      var ui = this.folders[folderObj.elementId];
      ui.msgNode.textContent = (folderObj.totalTime() / 1000).toFixed(2) + ' seconds';
    };

    DefaultUI.prototype.addSubFolder = function (folderObj, subFolderObj) {
      var folderUI = this.folders[folderObj.elementId], subFolderUI = this.folders[subFolderObj.elementId];
      folderUI.childUL.appendChild(subFolderUI.elem);
    };

    DefaultUI.prototype.addPage = function (folderObj, pageObj) {
      var folderUI = this.folders[folderObj.elementId], pageUI = this.pages[pageObj.elementId];
      folderUI.childUL.appendChild(pageUI.elem);
      folderUI.folderHeader.classList.add('hasPages');
    };

    DefaultUI.prototype.initializeReporter = function (reporterObj) {
      var resultElem = document.getElementById("results");
      var fullResultsElem = document.getElementById("fullresults");
      var node = this.localDoc.createTextNode('');
      fullResultsElem.appendChild(node);
      var fullResultsNode = node;
      
      var rootUI = this.folders[reporterObj.root.elementId];
      resultElem.appendChild(rootUI.elem);
      
      this.reporterUI = {
        resultElem: resultElem,
        fullResultsElem: fullResultsElem,
        fullResultsNode: fullResultsNode,
        testResultsTextElem: this.localDoc.getElementById("testResultsAsText"),
        showTextSummaryElem: this.localDoc.getElementById("showTextSummary"),
        currentPageElem: null
      };
    };
    DefaultUI.prototype.setReporterMessage = function (reporterObj, msg) {
      this.reporterUI.fullResultsNode.textContent = msg;
    };
    DefaultUI.prototype.setReporterText = function (reporterObj, tx) {
      var r = this.reporterUI.testResultsTextElem;
      while (r.firstChild)
        r.removeChild(r.firstChild);
      r.appendChild(this.createTextNode(tx));
      this.reporterUI.showTextSummaryElem.style.visibility = "visible";
    };
    
    var MiniUI = function (localDoc) {
      this.localDoc = localDoc;
      this.pages = {};
      this.folders = {};
      this.reporterUI = {};
    };
    
    MiniUI.prototype.createElement = function (tag) {
      return this.localDoc.createElement(tag);
    };
    
    MiniUI.prototype.createTextNode = function (data) {
      return this.localDoc.createTextNode(data);
    };
    
    MiniUI.prototype.initializePage = function (pageObj) {
      var url = pageObj.url;
      var li = this.createElement('li');
      li.id = pageObj.elementId;
      var div = this.createElement('div');
      div.classList.add('pageHeader');
      var a = this.createElement('a');
      a.href = WebGLTestHarnessModule.getURLWithOptions(url, {
        webglVersion: pageObj.reporter.selectedWebGLVersion,
        quiet: OPTIONS.quiet
      });
      a.title = url;
      a.target = "_blank";
      div.appendChild(a);
      li.setAttribute('class', 'testpage');
      li.appendChild(div);
      
      this.pages[pageObj.elementId] = {
        elem: li,
        checked: true
      };
    };
    
    MiniUI.prototype.addResult = function (pageObj, msg, success, skipped) {
    };

    MiniUI.prototype.pageChecked = function (pageObj) {
      return this.pages[pageObj.elementId].checked;
    };

    MiniUI.prototype.setChecked = function (pageObj, value) {
      var ui = this.pages[pageObj.elementId];
      ui.checked = value;
      if (value) {
        ui.elem.classList.remove('testpage-toskip');
      } else {
        ui.elem.classList.add('testpage-toskip');
      }
    };

    MiniUI.prototype.startPage = function (pageObj, shouldRun) {
      var ui = this.pages[pageObj.elementId];

      if (shouldRun) {
        ui.elem.classList.remove('testpagetimeout');
        ui.elem.classList.remove('testpageskipped');
        ui.elem.classList.remove('testpagefail');
        ui.elem.classList.remove('testpagesuccess');
      }
    };
    
    MiniUI.prototype.finishPage = function (pageObj, success) {
      var ui = this.pages[pageObj.elementId];
      
      if (pageObj.totalSkipped) {
        var msg = ' (' + pageObj.totalSkipped + ' of ' + pageObj.totalTests + ' skipped in ' + pageObj.totalTime.toFixed(1) + ' ms )';
      } else {
        var msg = ' (' + pageObj.totalSuccessful + ' of ' + pageObj.totalTests + ' passed in ' + pageObj.totalTime.toFixed(1) + ' ms )';
      }

      if (success === undefined) {
        var css = 'testpagetimeout';
        msg = '(*timeout*)';
      } else if (pageObj.totalSkipped) {
        var css = 'testpageskipped';
      } else if (pageObj.totalSuccessful != pageObj.totalTests) {
        var css = 'testpagefail';
      } else {
        var css = 'testpagesuccess';
      }
      
      ui.elem.classList.add(css);
    };
    
    MiniUI.prototype.markPageToRun = function (pageObj, value) {
      var ui = this.pages[pageObj.elementId];
      if (value) {
        ui.elem.classList.add('testpage-torun');
      } else {
        ui.elem.classList.remove('testpage-torun');
      }
    };    

    MiniUI.prototype.initializeFolder = function (folderObj) {
      var li = this.createElement('li');
      li.id = folderObj.elementId;
      li.classList.add("folder");
      li.title = folderObj.displayName;
      
      var ul = this.createElement('ul');
      li.appendChild(ul);
      
      this.folders[folderObj.elementId] = {
        childUL: ul,
        elem: li,
        checked: true
      };
    };
    
    MiniUI.prototype.folderChecked = function (folderObj) {
      return this.folders[folderObj.elementId].checked;
    };

    MiniUI.prototype.setFolderChecked = function (folderObj, value) {
      this.folders[folderObj.elementId].checked = value;
    };
    
    MiniUI.prototype.runFolder = function (folderObj) {
    };

    MiniUI.prototype.folderPageFinished = function (folderObj, page, success) {
    };

    MiniUI.prototype.addSubFolder = function (folderObj, subFolderObj) {
      var folderUI = this.folders[folderObj.elementId], subFolderUI = this.folders[subFolderObj.elementId];
      folderUI.childUL.appendChild(subFolderUI.elem);
    };

    MiniUI.prototype.addPage = function (folderObj, pageObj) {
      var folderUI = this.folders[folderObj.elementId], pageUI = this.pages[pageObj.elementId];
      folderUI.childUL.appendChild(pageUI.elem);
      folderUI.elem.classList.add('hasPages');
    };

    MiniUI.prototype.initializeReporter = function (reporterObj) {
      var resultElem = document.getElementById("results");
      var fullResultsElem = document.getElementById("fullresults");
      var node = this.localDoc.createTextNode('');
      fullResultsElem.appendChild(node);
      var fullResultsNode = node;
      
      var rootUI = this.folders[reporterObj.root.elementId];
      resultElem.appendChild(rootUI.elem);
      resultElem.classList.add('miniUI');
      
      this.reporterUI = {
        resultElem: resultElem,
        fullResultsElem: fullResultsElem,
        fullResultsNode: fullResultsNode,
        testResultsTextElem: this.localDoc.getElementById("testResultsAsText"),
        showTextSummaryElem: this.localDoc.getElementById("showTextSummary"),
        currentPageElem: null
      };
    };
    MiniUI.prototype.setReporterMessage = function (reporterObj, msg) {
      this.reporterUI.fullResultsNode.textContent = msg;
    };
    MiniUI.prototype.setReporterText = function (reporterObj, tx) {
      var r = this.reporterUI.testResultsTextElem;
      while (r.firstChild)
        r.removeChild(r.firstChild);
      r.appendChild(this.createTextNode(tx));
      this.reporterUI.showTextSummaryElem.style.visibility = "visible";
    };
    
    var Page = function (reporter, folder, testIndex, url) {
      this.reporter = reporter;
      this.folder = folder;
      this.url = url;
      this.totalTests = 0;
      this.totalSuccessful = 0;
      this.totalTimeouts = 0;
      this.totalSkipped = 0;
      this.testIndex = testIndex;
      this.startTime = 0;
      this.totalTime = 0;
      this.results = [];

      this.elementId = "page" + pageCount++;
      this.reporter.ui.initializePage(this);
    };

    Page.prototype.addResult = function (msg, success, skipped) {
      ++this.totalTests;
      if (success === undefined) {
        ++this.totalTimeouts;
        var successCode = "timeout";
      } else if (success) {
        if (skipped) {
          var successCode = "skipped";
          ++this.totalSkipped;
        } else {
          var successCode = "success";
          ++this.totalSuccessful;
        }
      } else {
        var successCode = "failed";
      }
      
      this.results.push({msg: msg.substr(0, 2000), success: successCode});
      
      if (successCode === "timeout") {
        sendReport('testTimeout', {
          url: this.url,
          pageNo: this.testIndex,
          testNo: this.totalTests,
          msg: msg.substr(0, 20000)
        });
      }
      
      this.reporter.ui.addResult(this, msg, success, skipped);
    };

    Page.prototype.startPage = function () {
      var shouldRun = this.reporter.ui.pageChecked(this) && this.folder.checked();
      
      this.totalTests = 0;
      this.totalSuccessful = 0;
      this.totalTimeouts = 0;
      this.totalTime = 0;

      if (shouldRun) {
        this.startTime = timer.getMillis();
      }
      
      this.reporter.ui.startPage(this, shouldRun);
      
      sendReport('startPage', {
        url: this.url,
        pageNo: this.testIndex,
        shouldRun: shouldRun
      });

      return shouldRun;
    };

    Page.prototype.firstTestIndex = function () {
      return this.testIndex;
    };

    Page.prototype.finishPage = function (success) {
      this.totalTime = timer.getMillis() - this.startTime;

      if (success === undefined) {
        ++this.totalTests;
        ++this.totalTimeouts;
        
        this.results.push({msg: '(*timeout*)', success: "timeout"});
      }
      
      sendReport('finishPage', {
        url: this.url,
        pageNo: this.testIndex,
        totalTime: this.totalTime,
        totalTests: this.totalTests,
        totalSuccessful: this.totalSuccessful,
        totalTimeouts: this.totalTimeouts,
        totalSkipped: this.totalSkipped,
        results: this.results
      });
      
      this.reporter.ui.finishPage(this, success);
      this.folder.pageFinished(this, success);
    };

    Page.prototype.enableTest = function (re) {
      if (this.url.match(re)) {
        this.reporter.ui.setChecked(this, true);
        this.folder.enableUp_();
      }
    };

    Page.prototype.disableTest = function (re) {
      if (this.url.match(re)) {
        this.reporter.ui.setChecked(this, false);
      }
    };
    
    Page.prototype.checked = function () {
      return this.reporter.ui.pageChecked(this);
    };
    
    Page.prototype.markToRun = function (value) {
      this.reporter.ui.markPageToRun(this, value);
    };
    
    
    var Folder = function (reporter, folder, depth, opt_name) {
      this.reporter = reporter;
      this.depth = depth;
      this.name = opt_name || "";
      this.displayName = this.name;
      if (folder && folder.displayName) {
        this.displayName = folder.displayName + '/' + this.displayName;
      }
      this.subFolders = {};
      this.pages = [];
      this.items = [];
      this.folder = folder;
      this.cachedTotalTime = 0;

      this.elementId = "folder" + folderCount++;
      this.reporter.ui.initializeFolder(this);
    };

    Folder.prototype.checked = function () {
      return this.reporter.ui.folderChecked(this) &&
              (this.folder ? this.folder.checked() : true);
    };

    Folder.prototype.firstTestIndex = function () {
      return this.items[0].firstTestIndex();
    };

    Folder.prototype.numChildren = function () {
      var numChildren = 0;
      for (var name in this.subFolders) {
        numChildren += this.subFolders[name].numChildren();
      }
      return numChildren + this.pages.length;
    };

    Folder.prototype.totalTime = function () {
      // Check to see if the cached total time needs to be recomputed
      if (this.cachedTotalTime == -1) {
        this.cachedTotalTime = 0;
        for (var name in this.subFolders) {
          this.cachedTotalTime += this.subFolders[name].totalTime();
        }
        for (var ii = 0; ii < this.pages.length; ++ii) {
          this.cachedTotalTime += this.pages[ii].totalTime;
        }
      }
      return this.cachedTotalTime;
    };

    Folder.prototype.run = function () {
      this.reporter.ui.runFolder(this);
      var firstTestIndex = this.firstTestIndex();
      var count = this.numChildren();
      log("run tests: " + firstTestIndex + " to " + (firstTestIndex + count - 1));
      testHarness.runTests({start: firstTestIndex, count: count});
    };

    Folder.prototype.pageFinished = function (page, success) {
      this.cachedTotalTime = -1;
      this.reporter.ui.folderPageFinished(this, page, success);
      if (this.folder) {
        this.folder.pageFinished(page, success);
      }
    };

    Folder.prototype.getSubFolder = function (name) {
      var subFolder = this.subFolders[name];
      if (subFolder === undefined) {
        subFolder = new Folder(this.reporter, this, this.depth + 1, name);
        this.subFolders[name] = subFolder;
        this.items.push(subFolder);
        this.reporter.ui.addSubFolder(this, subFolder);
      }
      return subFolder;
    };

    Folder.prototype.getOrCreateFolder = function (url) {
      var parts = url.split('/');
      var folder = this;
      for (var pp = 0; pp < parts.length - 1; ++pp) {
        folder = folder.getSubFolder(parts[pp]);
      }
      return folder;
    };

    Folder.prototype.addPage = function (page) {
      this.pages.push(page);
      this.items.push(page);
      this.reporter.ui.addPage(this, page);
    };

    Folder.prototype.disableTest = function (re, opt_forceRecurse) {
      var recurse = true;
      if (this.name.match(re)) {
        this.reporter.ui.setFolderChecked(this, false);
        recurse = opt_forceRecurse;
      }
      if (recurse) {
        for (var name in this.subFolders) {
          this.subFolders[name].disableTest(re, opt_forceRecurse);
        }
        for (var ii = 0; ii < this.pages.length; ++ii) {
          this.pages[ii].disableTest(re);
        }
      }
    };

    Folder.prototype.enableUp_ = function () {
      this.reporter.ui.setFolderChecked(this, true);
      var parent = this.folder;
      if (parent) {
        parent.enableUp_();
      }
    };

    Folder.prototype.enableTest = function (re) {
      if (this.name.match(re)) {
        this.enableUp_();
      }
      for (var name in this.subFolders) {
        this.subFolders[name].enableTest(re);
      }
      for (var ii = 0; ii < this.pages.length; ++ii) {
        this.pages[ii].enableTest(re);
      }
    };

    var Reporter = function (iframes, UiClass) {
      this.localDoc = document;
      this.iframes = iframes;
      this.totalPages = 0;
      this.pagesByURL = {};
      this.pagesByOrder = [];
      this.ui = new UiClass(this.localDoc);

      // Check to see if WebGL is supported
      var canvas = document.createElement("canvas");
      var ctx = create3DContext(canvas, null, 1);

      // Check to see if WebGL2 is supported
      var canvas2 = document.createElement("canvas");
      var ctx2 = create3DContext(canvas2, null, 2);

      this.noSelectedWebGLVersion = false;
      this.selectedWebGLVersion = WebGLTestHarnessModule.getMajorVersion(OPTIONS.version);
      if (this.selectedWebGLVersion == 2 && !ctx2) {
        this.noSelectedWebGLVersion = true;
      } else if (this.selectedWebGLVersion == 1 && !ctx) {
        this.noSelectedWebGLVersion = true;
      }

      // If the WebGL2 context could be created use it to get context info
      if (ctx2) {
        ctx = ctx2;
      }

      this.noWebGL = !ctx;

      this.contextInfo = {};
      this.root = new Folder(this, null, 0, "all");
      this.callbacks = {};
      this.startTime = 0;
      this.startDate = null;

      this.ui.initializeReporter(this);
      
      if (ctx) {
        this.contextInfo["VENDOR"] = ctx.getParameter(ctx.VENDOR);
        this.contextInfo["VERSION"] = ctx.getParameter(ctx.VERSION);
        this.contextInfo["RENDERER"] = ctx.getParameter(ctx.RENDERER);
        this.contextInfo["RED_BITS"] = ctx.getParameter(ctx.RED_BITS);
        this.contextInfo["GREEN_BITS"] = ctx.getParameter(ctx.GREEN_BITS);
        this.contextInfo["BLUE_BITS"] = ctx.getParameter(ctx.BLUE_BITS);
        this.contextInfo["ALPHA_BITS"] = ctx.getParameter(ctx.ALPHA_BITS);
        this.contextInfo["DEPTH_BITS"] = ctx.getParameter(ctx.DEPTH_BITS);
        this.contextInfo["STENCIL_BITS"] = ctx.getParameter(ctx.STENCIL_BITS);

        var ext = ctx.getExtension("WEBGL_debug_renderer_info");
        if (ext) {
          this.contextInfo["UNMASKED_VENDOR"] = ctx.getParameter(ext.UNMASKED_VENDOR_WEBGL);
          this.contextInfo["UNMASKED_RENDERER"] = ctx.getParameter(ext.UNMASKED_RENDERER_WEBGL);
        }
      }
    };

    Reporter.prototype.enableTest = function (name) {
      this.root.enableTest(name);
    };

    Reporter.prototype.disableTest = function (name) {
      this.root.disableTest(name);
    };

    Reporter.prototype.disableAllTests = function () {
      this.root.disableTest(".*", true);
    };

    Reporter.prototype.addEventListener = function (type, func) {
      if (!this.callbacks[type]) {
        this.callbacks[type] = [];
      }
      this.callbacks[type].push(func);
    };

    Reporter.prototype.executeListenerEvents_ = function (type) {
      var callbacks = this.callbacks[type].slice(0);
      for (var ii = 0; ii < callbacks.length; ++ii) {
        setTimeout(callbacks[ii], 0);
      }
    };

    Reporter.prototype.runTest = function (url) {
      var page = this.pagesByURL[url];
      testHarness.runTests({start: page.firstTestIndex(), count: 1});
    };

    Reporter.prototype.getFolder = function (url) {
      return this.root.getOrCreateFolder(url);
    };

    Reporter.prototype.addPage = function (url) {
      var folder = this.getFolder(url);
      var page = new Page(this, folder, this.totalPages, url);
      folder.addPage(page);
      ++this.totalPages;
      this.pagesByURL[url] = page;
      this.pagesByOrder.push(page);
    };

    Reporter.prototype.startPage = function (url) {
      var page = this.pagesByURL[url];
      return page.startPage();
    };

    Reporter.prototype.addResult = function (url, msg, success, skipped) {
      var page = this.pagesByURL[url];
      page.addResult(msg, success, skipped);
    };

    Reporter.prototype.finishPage = function (url, success) {
      var page = this.pagesByURL[url];
      page.finishPage(success);
      if (OPTIONS.dumpShaders == 1) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', "/finishIndividualTest", true);
        xhr.send(null);
      }
    };

    Reporter.prototype.displayFinalResults = function (msg, success) {
      if (success) {
        var totalTests = 0;
        var totalSuccessful = 0;
        var totalTimeouts = 0;
        var totalSkipped = 0;
        var totalTime = timer.getMillis() - this.startTime;
        for (var url in this.pagesByURL) {
          var page = this.pagesByURL[url];
          totalTests += page.totalTests;
          totalSuccessful += page.totalSuccessful;
          totalTimeouts += page.totalTimeouts;
          totalSkipped += page.totalSkipped;
        }
        var timeout = '';
        if (totalTimeouts > 0) {
          timeout = ', ' + totalTimeouts + ' timed out';
        }
        var msg = ' (' + totalSuccessful + ' of ' +
                totalTests + ' passed' + timeout + ')';
        this.ui.setReporterMessage(this, msg);

        // generate a text summary
        var tx = "";
        tx += "WebGL Conformance Test Results\n";
        tx += "Version " + OPTIONS.version + "\n";
        tx += "\n";
        tx += "-------------------\n\n";
        tx += "User Agent: " + (navigator.userAgent ? navigator.userAgent : "(navigator.userAgent is null)") + "\n";
        tx += "WebGL VENDOR: " + this.contextInfo["VENDOR"] + "\n";
        tx += "WebGL VERSION: " + this.contextInfo["VERSION"] + "\n";
        tx += "WebGL RENDERER: " + this.contextInfo["RENDERER"] + "\n";
        tx += "Unmasked VENDOR: " + this.contextInfo["UNMASKED_VENDOR"] + "\n";
        tx += "Unmasked RENDERER: " + this.contextInfo["UNMASKED_RENDERER"] + "\n";
        tx += "WebGL R/G/B/A/Depth/Stencil bits (default config): " + this.contextInfo["RED_BITS"] + "/" + this.contextInfo["GREEN_BITS"] + "/" + this.contextInfo["BLUE_BITS"] + "/" + this.contextInfo["ALPHA_BITS"] + "/" + this.contextInfo["DEPTH_BITS"] + "/" + this.contextInfo["STENCIL_BITS"] + "\n";
        tx += "\n";
        tx += "-------------------\n\n";
        tx += "Test Summary (" + totalTests + " total tests):\n";
        tx += "Tests ran in " + (totalTime / 1000.0).toFixed(2) + " seconds\n";
        tx += "Tests PASSED: " + totalSuccessful + "\n";
        tx += "Tests FAILED: " + (totalTests - totalSuccessful - totalSkipped) + "\n";
        tx += "Tests TIMED OUT: " + totalTimeouts + "\n";
        tx += "Tests SKIPPED: " + totalSkipped + "\n";
        tx += "\n";
        tx += "-------------------\n\n";
        if (totalSuccessful < totalTests) {
          tx += "Failures:\n\n";
          for (var url in this.pagesByURL) {
            var page = this.pagesByURL[url];
            var pageTotalFail = page.totalTests - page.totalSuccessful - page.totalSkipped;
            if (!(page.totalTests == 0 && page.totalTimeouts == 0) &&
                    pageTotalFail > 0)
            {
              tx += url + ": " + pageTotalFail + " tests failed";
              if (page.totalTimeouts)
                tx += " (" + page.totalTimeouts + " timed out)";
              tx += "\n";
            }
          }
        } else {
          tx += "All tests PASSED\n\n";
        }
        tx += "\n";
        tx += "-------------------\n\n";
        tx += "Complete Test Results (total / pass / fail / timeout / skipped):\n\n";
        for (var url in this.pagesByURL) {
          var page = this.pagesByURL[url];
          var pageTotalFail = page.totalTests - page.totalSuccessful - page.totalSkipped;
          if (!(page.totalTests == 0 && page.totalTimeouts == 0)) {
            tx += url + ": " + page.totalTests + " / " +
                    page.totalSuccessful + " / " + pageTotalFail + " / " + page.totalTimeouts + " / " + page.totalSkipped + "\n";
          }
        }
        tx += "\n";
        tx += "-------------------\n\n";
        tx += "Generated on: " + (new Date()).toString() + "\n";

        this.ui.setReporterText(this, tx);

        sendReport('finishTest:success', {
          startTime: this.startDate.toUTCString(),
          totalTime: totalTime,
          totalTests: totalTests,
          totalSuccessful: totalSuccessful,
          totalTimeouts: totalTimeouts,
          totalSkipped: totalSkipped
        });

        this.postResultsToServer(tx);
      } else {
        var e = document.getElementById("error");
        e.innerHTML = msg;
        
        sendReport('finishTest:error', {
          startTime: this.startDate.toUTCString(),
          msg: msg.substr(0, 20000)
        });
        
        this.postResultsToServer(msg);
      }
    };

    Reporter.prototype.postTestStartToServer = function (resultText) {
      this.startTime = timer.getMillis();
      this.startDate = new Date();

      var testPages = [], pbo = this.pagesByOrder;
      for (var i = 0; i < pbo.length; i++) {
        testPages.push({
          url: pbo[i].url,
          pageNo: pbo[i].testIndex
        });
      }
      
      sendReport.prototype.startRun();
      sendReport('startTest', {
        startTime: this.startDate.toUTCString(),
        start: (runOptions && runOptions.start) || 0,
        count: (runOptions && runOptions.count) || 0,
        totalCount: testHarness.files.length,
        version: OPTIONS.version,
        timeoutDelay: testHarness.timeoutDelay,
        context: this.contextInfo,
        userAgent: navigator.userAgent,
        platform: navigator.platform,
        testPages: testPages
      });
      
      if (OPTIONS.postResults == undefined || OPTIONS.postResults == 0) {
        return;
      }

      var xhr = new XMLHttpRequest();
      xhr.open('POST', "/start", true);
      xhr.send(null);
    };

    Reporter.prototype.postResultsToServer = function (resultText) {      
      if (OPTIONS.postResults == undefined || OPTIONS.postResults == 0) {
        return;
      }

      var xhr = new XMLHttpRequest();
      xhr.open('POST', "/finish", true);
      xhr.setRequestHeader("Content-Type", "text/plain");
      xhr.send(resultText);
    };

    Reporter.prototype.ready = function () {
      timer.start();
      
      var loading = document.getElementById("loading");
      loading.style.display = "none";
      if (!this.noSelectedWebGLVersion) {
        var button = document.getElementById("runTestsButton");
        button.disabled = false;
        this.executeListenerEvents_("ready");
      }
    };

    Reporter.prototype.reportFunc = function (type, url, msg, success, skipped) {
      switch (type) {
        case reportType.ADD_PAGE:
          return this.addPage(msg);
        case reportType.READY:
          return this.ready();
        case reportType.START_PAGE:
          return this.startPage(url);
        case reportType.TEST_RESULT:
          return this.addResult(url, msg, success, skipped);
        case reportType.FINISH_PAGE:
          return this.finishPage(url, success);
        case reportType.FINISHED_ALL_TESTS:
          return this.displayFinalResults(msg, success);
        default:
          throw 'unhandled';
          break;
      }
      ;
    };
    
    Reporter.prototype.markPagesToRun = function (runOptions) {
        var pages_start = runOptions.start || 0;
        var pages_count = runOptions.count || this.pagesByOrder.length;
        for (var i = 0; i < this.pagesByOrder.length; i++) {
          var page = this.pagesByOrder[i];
          if (i >= pages_start && i < (pages_start + pages_count)) {
            page.markToRun(true);
          } else {
            page.markToRun(false);
          }
        }
    };

    var getURLOptions = function (obj) {
      var s = window.location.href;
      var q = s.indexOf("?");
      var e = s.indexOf("#");
      if (e < 0) {
        e = s.length;
      }
      var query = s.substring(q + 1, e);
      var pairs = query.split("&");
      for (var ii = 0; ii < pairs.length; ++ii) {
        var keyValue = pairs[ii].split("=");
        var key = keyValue[0];
        var value = decodeURIComponent(keyValue[1]);
        obj[key] = value;
      }
    };

    getURLOptions(OPTIONS);
    
    if (OPTIONS.suiteID !== undefined && OPTIONS.suiteID != 0) {
      var suiteID = parseInt(OPTIONS.suiteID);
    } else {
      var suiteID = 0xDEADBEEF;
    }

    var makeVersionSelect = function (currentVersion) {
      var versionSelect = document.getElementById("testVersion");
      var foundCurrentVersion = false;
      var numericCurrentVersion = currentVersion.replace(/[^\d.]/g, '');

      for (var i in testVersions) {
        var version = testVersions[i];
        var numericVersion = version.replace(/[^\d.]/g, '');
        var option = document.createElement("option");
        option.setAttribute('value', numericVersion);
        option.innerHTML = version;

        if (numericVersion == numericCurrentVersion) {
          foundCurrentVersion = true;
          option.selected = true;
        }

        versionSelect.appendChild(option);
      }

      // If the version requested by the query string isn't in the list add it.
      if (!foundCurrentVersion) {
        var option = document.createElement("option");
        option.setAttribute('value', numericCurrentVersion);
        option.innerHTML = currentVersion + " (unknown)";
        option.selected = true;

        versionSelect.appendChild(option);
      }

      versionSelect.addEventListener('change', function (ev) {
        window.location.href = "?version=" + versionSelect.value;
      }, false);
    };

    makeVersionSelect(OPTIONS.version);
    
    var timer = new Timer();

    // Make iframes
    var makeIFrames = function () {
      var toparea = document.getElementById("toparea");
      var frame = document.getElementById("frames");
      var areaWidth = Math.max(100, toparea.clientWidth - 300);
      var areaHeight = Math.max(100, frame.clientHeight);

      var numCells = OPTIONS.frames;

      var gridWidth = Math.max(1, Math.ceil(Math.sqrt(numCells)));
      var gridHeight = gridWidth;
      var bestAspect = 99999;
      var bestNumEmptyCells = 99999;
      var bestNumEmptyCellsColumns = 0;
      var bestNumEmptyCellsAspect = 99999;
      var minGoodAspect = 1 / 3;
      var maxGoodAspect = 3 / 1;

      for (var columns = 1; columns <= numCells; ++columns) {
        var rows = Math.ceil(numCells / columns);
        var cellWidth = areaWidth / columns;
        var cellHeight = areaHeight / rows;
        var cellAspect = cellWidth / cellHeight;
        if (cellAspect >= minGoodAspect && cellAspect <= maxGoodAspect) {
          var numEmptyCells = columns * rows - numCells;
          // Keep the one with the least number of empty cells.
          if (numEmptyCells < bestNumEmptyCells) {
            bestNumEmptyCells = numEmptyCells;
            bestNumEmptyCellsColumns = columns;
            bestNumEmptyCellsAspect = cellAspect;
            // If it's the same number of empty cells keep the one
            // with the best aspect.
          } else if (numEmptyCells == bestNumEmptyCells &&
                  Math.abs(cellAspect - 1) <
                  Math.abs(bestNumEmptyCellsAspect - 1)) {
            bestNumEmptyCellsColumns = columns;
            bestNumEmptyCellsAspect = cellAspect;
          }
        }
        if (Math.abs(cellAspect - 1) < Math.abs(bestAspect - 1)) {
          gridWidth = columns;
          gridHeight = rows;
          bestAspect = cellAspect;
        }
      }

      // if we found an aspect with few empty cells use that.
      var numEmptyCells = gridWidth * gridHeight - numCells;
      if (bestNumEmptyCellsColumns && bestNumEmptyCells < numEmptyCells) {
        gridWidth = bestNumEmptyCellsColumns;
        gridHeight = Math.ceil(numCells / gridWidth);
      }

      var table = document.createElement("table");
      table.style.height = areaHeight + "px";
      var tbody = document.createElement("tbody");
      var iframes = [];
      for (var row = 0; row < gridHeight; ++row) {
        var tr = document.createElement("tr");
        for (var column = 0; column < gridWidth; ++column) {
          var td = document.createElement("td");
          if (numCells > 0) {
            --numCells;
            var iframe = document.createElement("iframe");
            iframe.setAttribute("scrolling", "yes");
            iframe.style.width = "100%";
            iframe.style.height = "100%";
            iframes.push(iframe);
            td.appendChild(iframe);
          }
          tr.appendChild(td);
        }
        tbody.appendChild(tr);
      }
      table.appendChild(tbody);
      frame.appendChild(table);
      return iframes;
    };
    var iframes = makeIFrames();

    var runOptions = {};
    runOptions.continue = OPTIONS.continue ? parseInt(OPTIONS.continue) : 0; 
    runOptions.start = OPTIONS.start ? parseInt(OPTIONS.start) : 0; 
    if (OPTIONS.count) {
        runOptions.count = parseInt(OPTIONS.count);
    }

    var testPath = "00_test_list.txt";
    if (OPTIONS.root) {
      testPath = OPTIONS.root + "/" + testPath;
    }
    
    var uiClassList = {
      'mini': MiniUI
    };
    
    var uiClassName = OPTIONS.ui || 'default';
    var uiClass = (uiClassName in uiClassList) ? uiClassList[uiClassName] : DefaultUI;

    var reporter = new Reporter(iframes, uiClass);
    var testHarness = new WebGLTestHarnessModule.TestHarness(
            iframes,
            testPath,
            function (type, url, msg, success, skipped) {
              return reporter.reportFunc(type, url, msg, success, skipped);
            },
            OPTIONS);
    reporter.addEventListener("ready", function () {
      // Set which tests to include.
      if (OPTIONS.include) {
        reporter.disableAllTests();
        var includes = OPTIONS.include.split(",");
        for (var ii = 0; ii < includes.length; ++ii) {
          reporter.enableTest(new RegExp(includes[ii]));
        }
      }
      // Remove tests based on skip=re1,re2 in URL.
      if (OPTIONS.skip) {
        var skips = OPTIONS.skip.split(",");
        for (var ii = 0; ii < skips.length; ++ii) {
          reporter.disableTest(new RegExp(skips[ii]));
        }
      }
      // Mark which pages are to be included in this test run
      reporter.markPagesToRun(runOptions);
      // Auto run the tests if the run=1 in URL
      if (OPTIONS.run != undefined && OPTIONS.run != 0) {
        reporter.postTestStartToServer();
        testHarness.runTests(runOptions);
      }
    });
    window.webglTestHarness = testHarness;
    var button = document.getElementById("runTestsButton");
    button.disabled = true;
    button.onclick = function () {
      autoScroll = autoScrollEnabled;
      reporter.postTestStartToServer();
      testHarness.runTests(runOptions);
    };
    var autoScrollCheckbox = document.getElementById("autoScrollCheckbox");
    autoScrollCheckbox.checked = autoScrollEnabled;
    autoScrollCheckbox.onclick = function () {
      autoScrollEnabled = autoScrollCheckbox.checked;
      autoScroll = autoScrollEnabled;
    };

    var hidePassedSheet = createStylesheet();
    var hidePassedCheckbox = document.getElementById("hidePassedCheckbox");
    hidePassedCheckbox.checked = false;
    hidePassedCheckbox.onclick = function () {
      var hidePassedTests = hidePassedCheckbox.checked;
      if (hidePassedTests) {
        hidePassedSheet.insertRule(".testpagesuccess { display: none; }", 0);
      } else {
        hidePassedSheet.deleteRule(0);
      }
    };

    var textbutton = document.getElementById("showTextSummary");
    textbutton.onclick = function () {
      log("click");
      var htmldiv = document.getElementById("testResultsHTML");
      var textdiv = document.getElementById("testResultsText");
      if (textdiv.style.display == "none") {
        textdiv.style.display = "block";
        htmldiv.style.display = "none";
        textbutton.setAttribute("value", "display html summary");
      } else {
        textdiv.style.display = "none";
        htmldiv.style.display = "block";
        textbutton.setAttribute("value", "display text summary");
      }
    };
    if (reporter.noSelectedWebGLVersion) {
      button.disabled = true;
    }
    if (reporter.noWebGL) {
      var elem = document.getElementById("nowebgl");
      elem.style.display = "";
      reporter.postResultsToServer("Browser does not appear to support WebGL");
    } else if (reporter.noSelectedWebGLVersion) {
      var elem = document.getElementById("noselectedwebgl");
      elem.style.display = "";
      reporter.postResultsToServer("Browser does not appear to support the selected version of WebGL");
    }
  }
</script>
</head>
<body onload="start()">

<div id="testlist">

        <div id="testResultsHTML">
          <ul id="results">
          </ul>
        </div>
        <div style="display: none;" id="testResultsText">
          <pre id="testResultsAsText"></pre>
        </div>

</div> <!-- end of container -->

<div id="header">

<table>
  <tr style="height: 300px;">
    <td>
      <table id="toparea">
        <tr>
          <td style="width: 300px">
            <div id="info">
              <img src="resources/webgl-logo.png" /><br />
              WebGL Conformance Test Runner<br/>
              Version 
              <select id="testVersion">
              </select>
              <br/>
              <a href="../../conformance-suites/"><i>(click here for previous versions)</i></a>
              <br/>
              <input type="button" value="run tests" id="runTestsButton"/>
              <br/>
              <input type="checkbox" id="autoScrollCheckbox"/>
              <label for="autoScrollCheckbox">auto scroll</label>
              <br/>
              <input type="checkbox" id="hidePassedCheckbox"/>
              <label for="hidePassedCheckbox">hide passed tests</label>
              <br/>
              <input type="button" style="visibility: hidden;" value="display text summary" id="showTextSummary"/>
              <div id="nowebgl" class="nowebgl" style="display: none;">
                This browser does not appear to support WebGL
              </div>
              <div id="noselectedwebgl" class="nowebgl" style="display: none;">
                This browser does not appear to support the selected version of WebGL
              </div>
            </div>
          </td>
        </tr>
        <tr>
          <td>
            <div id="loading">
              Loading Tests...
            </div>
            <div>
              Results:
              <span id="fullresults">
              </span>
            </div>
          </td>
        </tr>
        <tr>
          <td>
            <div id="error-wrap">
              <pre id="error"></pre>
            </div>
          </td>
        </tr>
      </table>
    </td>
    <td id="frames"></td>
  </tr>
</table>
</div> <!-- end of header -->

</body>
</html>
