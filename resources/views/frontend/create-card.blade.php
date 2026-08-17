@extends('layouts.campaign')

@section('title', 'Create Personalized Card')

@section('content')
    @php($selectedTemplate = old('card_template', session('card_template', 'chalkboard')))
    @php($templateImages = [
        'chalkboard' => asset('images/blackboard-card-template-v3.png'),
        'golden' => asset('images/golden-card-template-v3.png'),
        'notebook' => asset('images/notebook-card-template-v2.png'),
    ])
    <section class="card-maker section-wrap">
        <div class="card-form-copy">
            <div class="eyebrow"><span>03</span> Personalized greeting</div>
            <h1>Create something <em>only they can receive.</em></h1>
            <p>Your teacher's name, your own words and your professional details will create a unique downloadable card.</p>
            <form id="cardForm" class="premium-form" method="POST" action="{{ route('campaign.store-card') }}">
                @csrf
                <input id="renderedCard" type="hidden" name="rendered_card">
                <fieldset class="card-template-picker">
                    <legend>Choose a card template <span>*</span></legend>
                    <label><input type="radio" name="card_template" value="chalkboard" @checked($selectedTemplate === 'chalkboard')><span class="template-thumb template-chalkboard" style="background-image:url('{{ $templateImages['chalkboard'] }}')"><b>BLACKBOARD</b><small>Classroom tribute</small></span></label>
                    <label><input type="radio" name="card_template" value="golden" @checked($selectedTemplate === 'golden')><span class="template-thumb template-golden" style="background-image:url('{{ $templateImages['golden'] }}')"><b>CERTIFICATE</b><small>Formal appreciation</small></span></label>
                    <label><input type="radio" name="card_template" value="notebook" @checked($selectedTemplate === 'notebook')><span class="template-thumb template-notebook" style="background-image:url('{{ $templateImages['notebook'] }}')"><b>ELEGANT</b><small>Classic appreciation</small></span></label>
                </fieldset>
                <label>Teacher or mentor name <span>*</span><input id="teacherName" name="teacher_name" maxlength="80" value="" placeholder="Prof. Mehta" autocomplete="off" required></label>
                <label>Your message <span>*</span><textarea id="cardMessage" name="card_message" maxlength="240" rows="5" placeholder="Thank you for believing in me and guiding my journey..." autocomplete="off" required></textarea><small><output id="messageCount">0</output>/240 characters</small></label>
                <button class="btn-gold wide">Create my card →</button>
            </form>
        </div>
        <div id="cardPreview" class="unique-card-preview preview-{{ $selectedTemplate }}" style="background-image:url('{{ $templateImages[$selectedTemplate] }}')">
            <span id="cardKicker" class="card-kicker">HAPPY TEACHER'S DAY</span>
            <div id="cardSeal" class="guru-seal">GURU</div>
            <h2 id="previewTeacher">Dear Teacher,</h2>
            <p id="previewMessage">Your personal message will appear<br>here as you type.</p>
            <small>With gratitude,</small>
            <b>{{ $reel->doctor_name }}</b>
            <i>{{ $reel->city }}</i>
            <footer id="cardFooter">THE BEST TEACHERS HELP US REACH THE TOP</footer>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        const name = document.querySelector('#teacherName');
        const message = document.querySelector('#cardMessage');
        const previewName = document.querySelector('#previewTeacher');
        const previewMessage = document.querySelector('#previewMessage');
        const count = document.querySelector('#messageCount');
        const preview = document.querySelector('#cardPreview');
        const kicker = document.querySelector('#cardKicker');
        const seal = document.querySelector('#cardSeal');
        const footer = document.querySelector('#cardFooter');
        const templateImages = @json($templateImages);
        const form = document.querySelector('#cardForm');
        const renderedCard = document.querySelector('#renderedCard');
        const submitButton = form.querySelector('button[type="submit"], button:not([type])');
        name.addEventListener('input', () => previewName.textContent = `Dear ${name.value.trim() || 'Teacher'},`);
        const resizePreviewMessage = () => {
            const value = message.value.trim();
            const length = value.length;
            const isGolden = preview.classList.contains('preview-golden');
            const fontSize = isGolden
                ? (length === 0 ? 26 : length <= 30 ? 30 : length <= 60 ? 26 : length <= 110 ? 22 : length <= 170 ? 19 : 16)
                : (length === 0 || length <= 45 ? 30 : length <= 90 ? 25 : length <= 150 ? 21 : 17);
            previewMessage.style.fontSize = `${fontSize}px`;
            previewMessage.style.lineHeight = length > 150 ? '1.3' : '1.4';

            if (value) {
                previewMessage.textContent = value;
            } else {
                previewMessage.innerHTML = 'Your personal message will appear<br>here as you type.';
            }
        };
        message.addEventListener('input', () => {
            resizePreviewMessage();
            count.value = message.value.length;
        });
        resizePreviewMessage();
        document.querySelectorAll('[name="card_template"]').forEach(option => option.addEventListener('change', () => {
            preview.className = `unique-card-preview preview-${option.value}`;
            preview.style.backgroundImage = `url("${templateImages[option.value]}")`;
            const copy = {
                chalkboard: ["HAPPY TEACHER'S DAY", 'GURU', 'THE BEST TEACHERS HELP US REACH THE TOP'],
                golden: ['CERTIFICATE OF APPRECIATION', '★', 'PRESENTED WITH RESPECT AND GRATITUDE'],
                notebook: ["A NOTE FOR MY TEACHER", 'A+', 'THANK YOU FOR MAKING EVERY LESSON MATTER'],
            }[option.value];
            [kicker.textContent, seal.textContent, footer.textContent] = copy;
            name.value = '';
            message.value = '';
            name.dispatchEvent(new Event('input'));
            message.dispatchEvent(new Event('input'));
            resizePreviewMessage();
        }));
        name.dispatchEvent(new Event('input'));
        message.dispatchEvent(new Event('input'));
        document.querySelector('[name="card_template"]:checked')?.dispatchEvent(new Event('change'));

        let previewCaptured = false;
        form.addEventListener('submit', async event => {
            if (previewCaptured) return;

            event.preventDefault();
            submitButton.disabled = true;
            submitButton.textContent = 'Creating your card...';

            try {
                await document.fonts.ready;
                if (!window.cardToPng) throw new Error('Card renderer is not ready.');

                const scale = 1080 / preview.offsetWidth;
                await new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve)));
                renderedCard.value = await window.cardToPng(preview, {
                    cacheBust: true,
                    pixelRatio: scale,
                    width: preview.offsetWidth,
                    height: preview.offsetHeight,
                });
                previewCaptured = true;
                form.submit();
            } catch (error) {
                submitButton.disabled = false;
                submitButton.textContent = 'Create my card →';
                alert('Card preview could not be prepared. Please try again.');
            }
        });

        window.addEventListener('pageshow', () => {
            previewCaptured = false;
            renderedCard.value = '';
            submitButton.disabled = false;
            submitButton.textContent = 'Create my card →';
            name.value = '';
            message.value = '';
            const firstTemplate = document.querySelector('[name="card_template"][value="chalkboard"]');
            firstTemplate.checked = true;
            name.dispatchEvent(new Event('input'));
            message.dispatchEvent(new Event('input'));
            firstTemplate.dispatchEvent(new Event('change'));
        });
    </script>
@endpush
