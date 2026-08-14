@extends('layouts.campaign')
@section('title', 'Record Your Message')
@section('content')
<section class="record-page section-wrap"><div class="record-copy"><div class="eyebrow"><span>02</span> Record your message</div><h1>Speak from the <em>heart.</em></h1><p>Look into the camera and share a short message for the teacher who inspired you.</p><div class="prompt-box"><b>Need a little inspiration?</b><p>“Thank you for believing in me and showing me what was possible...”</p></div></div>
<div class="recorder-shell"><div class="camera-stage"><video id="camera" autoplay muted playsinline></video><div id="faceGuide" class="face-guide"></div><div id="countdown" class="countdown"></div><span id="timer" class="timer">00:00</span><span id="faceStatus" class="face-status" aria-live="polite">Position one face inside the guide</span><span class="camera-hint">Keep your full face inside the square</span></div>
<div class="zoom-control"><label for="zoomRange">Face zoom</label><button type="button" id="zoomOut" aria-label="Zoom face out">−</button><input id="zoomRange" type="range" min="1.00" max="1.50" value="1" step="0.05"><button type="button" id="zoomIn" aria-label="Zoom face in">+</button><output id="zoomValue">100%</output></div>
<form id="uploadForm" method="POST" action="{{ route('campaign.upload') }}" enctype="multipart/form-data">@csrf<input id="recordingInput" type="file" name="recording" hidden><input id="videoZoom" type="hidden" name="video_zoom" value="1"><div class="recorder-actions"><button type="button" id="startBtn" class="record-btn"><span></span> Start recording</button><button type="button" id="stopBtn" class="btn-outline" hidden>Stop</button><button type="button" id="retakeBtn" class="btn-outline" hidden>Retake</button><button type="submit" id="continueBtn" class="btn-gold" hidden>Use this recording →</button></div></form><p class="recording-limit">Maximum recording time is 20 seconds. Recording will stop automatically.</p><p id="recordError" class="record-error"></p></div></section>
<div id="processingOverlay" class="submit-processing" hidden><div class="processing-orb"><span>✦</span></div><h2>Creating your reel...</h2><p>Please keep this page open. Your preview will appear automatically.</p><div class="progress-track"><i></i></div></div>
@endsection
@push('scripts')<script>
const video=document.querySelector('#camera'),start=document.querySelector('#startBtn'),stop=document.querySelector('#stopBtn'),retake=document.querySelector('#retakeBtn'),go=document.querySelector('#continueBtn'),input=document.querySelector('#recordingInput'),timer=document.querySelector('#timer'),count=document.querySelector('#countdown'),error=document.querySelector('#recordError'),faceStatus=document.querySelector('#faceStatus'),faceGuide=document.querySelector('#faceGuide');let stream,recorder,chunks=[],recordedBlob=null,seconds=0,tick,faceDetector,faceDetectionFrame,detecting=false,faceDetectionActive=false;
const zoomRange=document.querySelector('#zoomRange'),zoomValue=document.querySelector('#zoomValue'),videoZoom=document.querySelector('#videoZoom'),zoomButtons=[document.querySelector('#zoomOut'),document.querySelector('#zoomIn')];
function applyZoom(){const zoom=Number(zoomRange.value);video.style.setProperty('--camera-zoom',zoom);videoZoom.value=zoom.toFixed(2);zoomValue.value=Math.round(zoom*100)+'%'}
zoomRange.addEventListener('input',applyZoom);zoomButtons[0].onclick=()=>{zoomRange.stepDown();applyZoom()};zoomButtons[1].onclick=()=>{zoomRange.stepUp();applyZoom()};applyZoom();
function stopFaceDetection(){faceDetectionActive=false;cancelAnimationFrame(faceDetectionFrame)}
async function detectFace(){
    if(!faceDetectionActive)return;
    if(!faceDetector||!stream||video.readyState<2){faceDetectionFrame=requestAnimationFrame(detectFace);return}
    if(detecting){faceDetectionFrame=requestAnimationFrame(detectFace);return}
    detecting=true;
    try{
        const faces=await faceDetector.detect(video);if(!faceDetectionActive){detecting=false;return}
        faceGuide.classList.toggle('face-ready',faces.length===1);
        faceGuide.classList.toggle('face-warning',faces.length>1);
        if(faces.length===1){
            const box=faces[0].boundingBox,faceRatio=Math.max(box.width/video.videoWidth,box.height/video.videoHeight);
            const desired=Math.min(1.5,Math.max(1,0.52/Math.max(faceRatio,0.01)));
            const current=Number(zoomRange.value),next=Math.round((current+(desired-current)*0.18)*20)/20;
            zoomRange.value=String(next);applyZoom();faceStatus.textContent='One face detected · framing adjusted';
        }else if(faces.length>1){faceStatus.textContent='More than one face detected';}
        else{faceStatus.textContent='No face detected · move into the guide';}
    }catch(e){faceDetector=null;faceStatus.textContent='Use the zoom controls to fit your face';}
    detecting=false;if(faceDetectionActive)faceDetectionFrame=requestAnimationFrame(detectFace);
}
document.querySelector('#uploadForm').addEventListener('submit',async event=>{
    event.preventDefault();
    if(!recordedBlob){error.textContent='Recording file is not ready. Please record again.';return}
    const sizeMb=recordedBlob.size/1024/1024;
    if(sizeMb>48){error.textContent='Recording is too large to upload. Please record again.';return}
    error.textContent='Uploading '+sizeMb.toFixed(1)+' MB recording. Please keep this page open...';
    go.disabled=true;go.textContent='Uploading...';document.querySelector('#processingOverlay').hidden=false;stream?.getTracks().forEach(track=>track.stop());
    const data=new FormData();
    data.append('_token',document.querySelector('meta[name="csrf-token"]').content);
    data.append('recording',recordedBlob,'recording.webm');
    data.append('video_zoom',videoZoom.value);
    try{
        const response=await fetch(document.querySelector('#uploadForm').action,{method:'POST',body:data,credentials:'same-origin',headers:{Accept:'text/html'}});
        if(response.redirected){window.location.assign(response.url);return}
        if(!response.ok)throw new Error('Upload failed (HTTP '+response.status+').');
        window.location.assign(@json(route('campaign.processing')));
    }catch(e){
        document.querySelector('#processingOverlay').hidden=true;go.disabled=false;go.textContent='Use this recording →';error.textContent=e.message+' Please retry or reload the page.';
    }
});
async function camera(){try{stream=await navigator.mediaDevices.getUserMedia({video:{facingMode:'user',width:{ideal:720},height:{ideal:720},aspectRatio:{ideal:1}},audio:true});video.srcObject=stream;if('FaceDetector'in window){faceDetector=new FaceDetector({fastMode:true,maxDetectedFaces:2});faceStatus.textContent='Looking for one face...';stopFaceDetection();faceDetectionActive=true;detectFace()}else{faceStatus.textContent='Use the zoom controls to fit your face'}}catch(e){error.textContent='Camera access is needed. Please allow camera and microphone permissions.'}}camera();
start.onclick=async()=>{try{error.textContent='';if(!stream)throw new Error('Camera is not ready');stopFaceDetection();zoomRange.disabled=true;zoomButtons.forEach(button=>button.disabled=true);for(let n=3;n>0;n--){count.textContent=n;await new Promise(r=>setTimeout(r,700))}count.textContent='';chunks=[];recordedBlob=null;seconds=0;const mimeType=MediaRecorder.isTypeSupported('video/webm;codecs=vp8,opus')?'video/webm;codecs=vp8,opus':'video/webm';recorder=new MediaRecorder(stream,{mimeType,videoBitsPerSecond:500000,audioBitsPerSecond:64000});recorder.onerror=e=>{error.textContent='Recording failed: '+(e.error?.message||'please try again.');start.hidden=false;stop.hidden=true};recorder.ondataavailable=e=>{if(e.data.size)chunks.push(e.data)};recorder.onstop=()=>{recordedBlob=new Blob(chunks,{type:recorder.mimeType||'video/webm'});if(!recordedBlob.size){error.textContent='No video was recorded. Please try again.';return}video.srcObject=null;video.src=URL.createObjectURL(recordedBlob);video.controls=true;error.textContent='Recording ready ('+(recordedBlob.size/1024/1024).toFixed(1)+' MB).';retake.hidden=false;go.hidden=false};recorder.start(1000);start.hidden=true;stop.hidden=false;stop.disabled=true;tick=setInterval(()=>{seconds++;timer.textContent='00:'+String(seconds).padStart(2,'0');if(seconds>=5)stop.disabled=false;if(seconds>=20)stop.click()},1000)}catch(e){error.textContent=e.message||'Recording could not start. Please reload and try again.';zoomRange.disabled=false;zoomButtons.forEach(button=>button.disabled=false)}};
stop.onclick=()=>{if(stop.disabled)return;clearInterval(tick);recorder.stop();stop.hidden=true};retake.onclick=()=>{video.controls=false;video.src='';retake.hidden=go.hidden=true;start.hidden=false;stop.disabled=true;timer.textContent='00:00';zoomRange.disabled=false;zoomButtons.forEach(button=>button.disabled=false);camera()};
</script>@endpush
