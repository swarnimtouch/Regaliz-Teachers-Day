@extends('layouts.campaign')

@section('title', 'Record Audio Message')

@section('content')
<section class="audio-page section-wrap">
    <div class="audio-copy">
        <div class="eyebrow"><span>03</span> Audio message</div>
        <h1>Let your gratitude be <em>heard.</em></h1>
        <p>Speak naturally. Your voice will play behind this Teacher's Day banner for the complete recording duration.</p>
        <div class="prompt-box"><b>You can begin with:</b><p>“Dear teacher, thank you for guiding me and inspiring my journey...”</p></div>
    </div>
    <div class="audio-recorder">
        <img src="{{ asset('images/holding-banner-audio.png') }}" alt="Teacher's Day audio holding banner">
        <canvas id="audioWave" width="720" height="110"></canvas>
        <div class="audio-status"><span id="audioDot"></span><b id="audioStatus">Ready to record</b><time id="audioTimer">00:00</time></div>
        <form id="audioForm" method="POST" action="{{ route('campaign.upload-audio') }}" enctype="multipart/form-data">@csrf<input id="audioInput" type="file" name="audio" hidden><div class="recorder-actions"><button id="audioStart" type="button" class="record-btn"><span></span> Start audio</button><button id="audioStop" type="button" class="btn-outline" hidden>Stop recording</button><button id="audioRetake" type="button" class="btn-outline" hidden>Retake</button><button id="audioContinue" type="submit" class="btn-gold" hidden>Generate message →</button></div></form>
        <audio id="audioPreview" controls hidden></audio><p id="audioError" class="record-error"></p><p class="recording-limit recording-limit-light">Maximum recording time is 20 seconds. Recording will stop automatically.</p>
    </div>
</section>
<div id="processingOverlay" class="submit-processing" hidden><div class="processing-orb"><span>♪</span></div><h2>Creating your audio message...</h2><p>The video will match your audio duration.</p><div class="progress-track"><i></i></div></div>
@endsection

@push('scripts')
<script>
const start=document.querySelector('#audioStart'),stop=document.querySelector('#audioStop'),retake=document.querySelector('#audioRetake'),go=document.querySelector('#audioContinue'),input=document.querySelector('#audioInput'),preview=document.querySelector('#audioPreview'),statusText=document.querySelector('#audioStatus'),timer=document.querySelector('#audioTimer'),dot=document.querySelector('#audioDot'),canvas=document.querySelector('#audioWave'),ctx=canvas.getContext('2d'),error=document.querySelector('#audioError');let stream,recorder,chunks=[],seconds=0,tick,audioContext,analyser,animation;
function drawWave(){const data=new Uint8Array(analyser.fftSize);analyser.getByteTimeDomainData(data);ctx.clearRect(0,0,canvas.width,canvas.height);ctx.strokeStyle='#f4d51f';ctx.lineWidth=3;ctx.beginPath();data.forEach((value,index)=>{const x=index/(data.length-1)*canvas.width;const y=value/255*canvas.height;index?ctx.lineTo(x,y):ctx.moveTo(x,y)});ctx.stroke();animation=requestAnimationFrame(drawWave)}
start.onclick=async()=>{try{stream=await navigator.mediaDevices.getUserMedia({audio:{echoCancellation:true,noiseSuppression:true}});audioContext=new AudioContext();analyser=audioContext.createAnalyser();analyser.fftSize=256;audioContext.createMediaStreamSource(stream).connect(analyser);drawWave();chunks=[];seconds=0;recorder=new MediaRecorder(stream);recorder.ondataavailable=e=>chunks.push(e.data);recorder.onstop=()=>{const type=recorder.mimeType||'audio/webm';const blob=new Blob(chunks,{type});const file=new File([blob],'message.webm',{type});const transfer=new DataTransfer();transfer.items.add(file);input.files=transfer.files;preview.src=URL.createObjectURL(blob);preview.hidden=false;retake.hidden=false;go.hidden=false;statusText.textContent='Recording ready';dot.classList.remove('live')};recorder.start();start.hidden=true;stop.hidden=false;dot.classList.add('live');statusText.textContent='Recording your message';tick=setInterval(()=>{seconds++;timer.textContent='00:'+String(seconds).padStart(2,'0');if(seconds>=20)stop.click()},1000)}catch(e){error.textContent='Please allow microphone access to record your message.'}};
stop.onclick=()=>{clearInterval(tick);cancelAnimationFrame(animation);recorder.stop();stream.getTracks().forEach(track=>track.stop());stop.hidden=true};retake.onclick=()=>{preview.hidden=true;preview.src='';retake.hidden=go.hidden=true;start.hidden=false;timer.textContent='00:00';statusText.textContent='Ready to record';ctx.clearRect(0,0,canvas.width,canvas.height)};
document.querySelector('#audioForm').addEventListener('submit',()=>{go.disabled=true;document.querySelector('#processingOverlay').hidden=false});
</script>
@endpush
