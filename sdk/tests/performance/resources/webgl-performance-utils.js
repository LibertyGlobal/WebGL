var WebGLPerformanceUtils = (function() {
  "use strict";
  
  var wtu = WebGLTestUtils;
  
  var isUndefined = function(value) {
    return value === undefined;
  };
  
  var getDefaultValue = function(value, defaultValue) {
    return !isUndefined(value) ? value : defaultValue;
  };
  
  var renderFrames = function(num_frames, render_fn, progress_fn){
    var render_count = 0;
    
    var do_interval = function() {
      render_count++;
      
      if (render_count <= num_frames) {
        if (render_count === num_frames) {
          clearInterval(interval_id);
        }
        render_fn();
        progress_fn(render_count, num_frames);
      }
    };
    
    var interval_id = setInterval(do_interval, 1);
  };
  
  var makeReportProgressFn = function(progressCnt) {
    return function(num_rendered, num_total) {
      if (num_rendered === num_total) {
        testPassed("All draw calls completed successfully");
        finishTest();
      } else if ((num_rendered > 0) && (num_rendered % progressCnt === 0)) {
        // Needed to avoid test timeout within the harness on some slower platforms
        testPassed("Completed " + num_rendered + " / " + num_total + " iterations");
      }
    };
  };
  
  var runTest = function(info, setupFn) {
    var canvasName = getDefaultValue(info.canvas, 'canvas');
    var numFrames = getDefaultValue(info.numFrames, 3600);
    
    var runTestCore = function(images) {
      var gl = wtu.create3DContext(canvasName);
      if (!gl) {
        testFailed('create3DContext');
        return;
      }

      var program = setupProgram(gl, info);
      if (!program) {
        testFailed('setupProgram');
        return;
      }
      gl.useProgram(program.programID);

      var renderFn = setupFn(gl, program, images, info);
      if (!renderFn) {
        testFailed('setupFn');
        return;
      }

      var renderFnWrap = function() {
        renderFn(gl, program, images, info);
        gl.finish();
      };

      var progressFn = makeReportProgressFn(Math.ceil(numFrames / 100));
      renderFrames(numFrames, renderFnWrap, progressFn);
    };
  
    window.addEventListener("load", function() {
      wtu.loadImagesAsync(info.images || [], function(images_map) {
        runTestCore(images_map);
      });
    }, false);
  };
  
  var setupProgram = function(gl, info, opt_errorCallback, opt_logShaders) {
    var errFn = opt_errorCallback || testFailed;
    
    if (isUndefined(info.vShaderSource)) {
      if (info.vShaderId) {
        info.vShaderSource = wtu.getScript(info.vShaderId);
      } else if (info.vShaderFile) {
        info.vShaderSource = wtu.readFile(info.vShaderFile);
      } else {
        errFn("setupProgram: vertex shader source not specified");
        return null;
      }
    }
    
    if (isUndefined(info.fShaderSource)) {
      if (info.fShaderId) {
        info.fShaderSource = wtu.getScript(info.fShaderId);
      } else if (info.fShaderFile) {
        info.fShaderSource = wtu.readFile(info.fShaderFile);
      } else {
        errFn("setupProgram: fragment shader source not specified");
        return null;
      }
    }
    
    var vs = wtu.loadShader(gl, info.vShaderSource, gl.VERTEX_SHADER, errFn, opt_logShaders);
    var fs = wtu.loadShader(gl, info.fShaderSource, gl.FRAGMENT_SHADER, errFn, opt_logShaders);

    if (vs && fs) {
      var programID = wtu.createProgram(gl, vs, fs, errFn);
      return {
        programID: programID,
        attributes: wtu.getAttribMap(gl, programID),
        uniforms: wtu.getUniformMap(gl, programID)
      };
    }
    
    return null;
  };
  
  return {
    isUndefined: isUndefined,
    getDefaultValue: getDefaultValue,
    setupProgram: setupProgram,
    renderFrames: renderFrames,
    runTest: runTest
  };
})();
