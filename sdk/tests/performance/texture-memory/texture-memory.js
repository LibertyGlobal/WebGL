var WebGLTextureMemoryTests = (function() {
  "use strict";
  
  var wtu = WebGLTestUtils;
  
  var simpleVertexShader = [    
    'attribute vec4 aVertexPosition;',
    'attribute vec2 aTextureCoord;',
    'uniform vec3 uVertexOffset;',
    'uniform float uScale;',
    'varying vec2 vTextureCoord;',
    'void main(void) {',
    '  gl_Position   = vec4(aVertexPosition.xyz * uScale + uVertexOffset.xyz, aVertexPosition.w);',
    '  vTextureCoord = aTextureCoord;',
    '}'
  ].join('\n');
  
  var simpleFragmentShader = [    
    'precision mediump float;',
    'varying vec2 vTextureCoord;',
    'uniform sampler2D texture0;',
    'void main(void) {',
    '  gl_FragColor = texture2D(texture0, vTextureCoord);',
    '}'
  ].join('\n');
  
  var simpleQuadInfo = {
    vertices: [
      -1.0,  1.0,
       1.0,  1.0,
       1.0, -1.0,
      -1.0, -1.0
    ],
    texcoords: [
      0.0, 0.0,
      1.0, 0.0,
      1.0, 1.0,
      0.0, 1.0
    ],
    indices: [
      0, 1, 2,
      0, 2, 3
    ]
  };
  
  var buildTexturedIndexedQuad = function(gl) {
    var vertexBuffer = gl.createBuffer();
    gl.bindBuffer(gl.ARRAY_BUFFER, vertexBuffer);
    gl.bufferData(gl.ARRAY_BUFFER, new Float32Array(simpleQuadInfo.vertices), gl.STATIC_DRAW);

    var texcoordBuffer = gl.createBuffer();
    gl.bindBuffer(gl.ARRAY_BUFFER, texcoordBuffer);
    gl.bufferData(gl.ARRAY_BUFFER, new Float32Array(simpleQuadInfo.texcoords), gl.STATIC_DRAW);
    
    var indexBuffer = gl.createBuffer();
    gl.bindBuffer(gl.ELEMENT_ARRAY_BUFFER, indexBuffer);
    gl.bufferData(gl.ELEMENT_ARRAY_BUFFER, new Uint16Array(simpleQuadInfo.indices), gl.STATIC_DRAW);
    
    var draw = function(textureID, vertices_loc, texcoords_loc) {
      gl.activeTexture(gl.TEXTURE0);
      gl.bindTexture(gl.TEXTURE_2D, textureID);
      
      gl.bindBuffer(gl.ARRAY_BUFFER, vertexBuffer);
      gl.enableVertexAttribArray(vertices_loc);
      gl.vertexAttribPointer(vertices_loc, 2, gl.FLOAT, false, 0, 0);
      
      gl.bindBuffer(gl.ARRAY_BUFFER, texcoordBuffer);
      gl.enableVertexAttribArray(texcoords_loc);
      gl.vertexAttribPointer(texcoords_loc, 2, gl.FLOAT, false, 0, 0);
      
      gl.bindBuffer(gl.ELEMENT_ARRAY_BUFFER, indexBuffer);
      gl.drawElements(gl.TRIANGLES, 6, gl.UNSIGNED_SHORT, 0);
    };
    
    return {
      vertexBuffer: vertexBuffer,
      texcoordBuffer: texcoordBuffer,
      indexBuffer: indexBuffer,
      draw: draw
    };
  };
  
  var createStandardTexture = function(gl) {
    var textureID = gl.createTexture();
    gl.bindTexture(gl.TEXTURE_2D, textureID);
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE);
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR);
    return textureID;
  };
  
  var uploadTexture = function(gl, texture, image) {
    gl.bindTexture(gl.TEXTURE_2D, texture);
    gl.pixelStorei(gl.UNPACK_FLIP_Y_WEBGL, true);
    gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, gl.RGBA, gl.UNSIGNED_BYTE, image);
  };
  
  var forEachInGrid = function(gridX, gridY, callbackFn) {
    var gridScaleX = 1.0 / gridX;
    var gridStartX = -1.0 + gridScaleX;
    var gridStepX = 2 * gridScaleX;
    var gridScaleY = 1.0 / gridY;
    var gridStartY = -1.0 + gridScaleY;
    var gridStepY = 2 * gridScaleY;
    
    var index = 0;
    for (var yy = 0; yy < gridY; yy++) {
      for (var xx = 0; xx < gridX; xx++) {
        var posX = gridStartX + xx * gridStepX;
        var posY = gridStartY + yy * gridStepY;
        callbackFn(posX, posY, xx, yy, index);
        index++;
      }
    }
  };
  
  return {
    buildTexturedIndexedQuad: buildTexturedIndexedQuad,
    createStandardTexture: createStandardTexture,
    uploadTexture: uploadTexture,
    forEachInGrid: forEachInGrid,
    simpleVertexShader: simpleVertexShader,
    simpleFragmentShader: simpleFragmentShader
  };
})();
