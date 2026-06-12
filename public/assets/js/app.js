/* ============================================================
   app.js — Marvel Trivia: Quiz Controller
   Stages: setup -> play -> results
   ============================================================ */

(function () {
    'use strict';

    const setupStage   = document.getElementById('setup-stage');
    if (!setupStage) return; // not on game page

    const heroId       = setupStage.dataset.heroId;
    const heroName     = setupStage.dataset.heroName;
    const isLoggedIn   = !!window.__QUIZ_USER_LOGGED_IN__;

    // Localised strings injected by the page; t() looks up a key and applies
    // sprintf-style %s substitution. Falls back to the key when absent.
    const STRINGS = window.__I18N__ || {};
    function t(key) {
        let str = STRINGS[key] != null ? STRINGS[key] : key;
        const args = Array.prototype.slice.call(arguments, 1);
        // Use a function replacement so values containing "$" are inserted literally.
        args.forEach((a) => { str = str.replace('%s', () => String(a)); });
        return str;
    }

    // Setup elements
    const difficultyCards = document.querySelectorAll('.difficulty-card');
    const startBtn        = document.getElementById('start-quiz');
    const setupHint       = document.getElementById('setup-hint');

    // Play elements
    const playStage     = document.getElementById('play-stage');
    const hudReward     = document.getElementById('hud-reward');
    const hudLives      = document.getElementById('hud-lives');
    const hudProgress   = document.getElementById('hud-progress');
    const hudTimer      = document.getElementById('hud-timer');
    const timerBarFill  = document.getElementById('timer-bar-fill');
    const questionText  = document.getElementById('question-text');
    const answerGrid    = document.getElementById('answer-grid');
    const feedbackBox   = document.getElementById('question-feedback');
    const nextBtn       = document.getElementById('next-question');

    // Results elements
    const resultsStage     = document.getElementById('results-stage');
    const resultsEyebrow   = document.getElementById('results-eyebrow');
    const resultsTitle     = document.getElementById('results-title');
    const resultsScore     = document.getElementById('results-score');
    const resultsRing      = document.getElementById('results-ring');
    const resultsCorrect   = document.getElementById('results-correct');
    const resultsAccuracy  = document.getElementById('results-accuracy');
    const resultsTime      = document.getElementById('results-time');
    const resultsSaved     = document.getElementById('results-saved');
    const resultsBreakdown = document.getElementById('results-breakdown');
    const playAgainBtn     = document.getElementById('play-again');

    const SECONDS_PER_QUESTION = 15;
    const MAX_LIVES = 3;
    const POINTS_BY_DIFFICULTY = { easy: 10, medium: 20, hard: 30 };

    let selectedDifficulty = null;
    let questions = [];
    let currentIndex = 0;
    let answers = {};                 // { question_id: 'A'|'B'|'C'|'D' }
    let livesRemaining = MAX_LIVES;
    let timerInterval = null;
    let questionStartedAt = 0;
    let quizStartedAt = 0;

    // ─────────────────────────────────────────────────────────────
    // Setup stage: difficulty selection
    // ─────────────────────────────────────────────────────────────
    difficultyCards.forEach((card) => {
        card.addEventListener('click', () => selectDifficulty(card));
        card.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                selectDifficulty(card);
            }
        });
    });

    function selectDifficulty(card) {
        difficultyCards.forEach((c) => {
            c.classList.remove('selected');
            c.setAttribute('aria-checked', 'false');
        });
        card.classList.add('selected');
        card.setAttribute('aria-checked', 'true');
        selectedDifficulty = card.dataset.difficulty;
        startBtn.disabled = false;
        setupHint.textContent = t('js.ready');
        setupHint.classList.remove('setup-hint--error');
    }

    startBtn.addEventListener('click', startQuiz);

    async function startQuiz() {
        if (!selectedDifficulty) {
            setupHint.textContent = t('js.pickDifficulty');
            setupHint.classList.add('setup-hint--error');
            return;
        }

        startBtn.disabled = true;
        startBtn.textContent = t('js.loading');

        try {
            const url = `api/question.php?hero_id=${encodeURIComponent(heroId)}&difficulty=${encodeURIComponent(selectedDifficulty)}`;
            const resp = await fetch(url, { credentials: 'same-origin' });
            const data = await resp.json();

            if (!resp.ok || data.error) {
                throw new Error(data.error || t('js.couldNotLoad'));
            }

            questions = data.questions;
            currentIndex = 0;
            answers = {};
            livesRemaining = MAX_LIVES;
            quizStartedAt = Date.now();

            hudReward.textContent = (POINTS_BY_DIFFICULTY[selectedDifficulty] || 0) + ' ' + t('js.pts');

            setupStage.hidden = true;
            playStage.hidden = false;

            updateHud();
            renderQuestion();
        } catch (err) {
            setupHint.textContent = err.message || t('js.couldNotLoad');
            setupHint.classList.add('setup-hint--error');
            startBtn.disabled = false;
            startBtn.textContent = t('js.startQuiz');
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Play stage
    // ─────────────────────────────────────────────────────────────
    function renderQuestion() {
        clearTimer();
        const q = questions[currentIndex];

        questionText.textContent = q.question_text;
        answerGrid.innerHTML = '';
        feedbackBox.hidden = true;
        feedbackBox.className = 'question-feedback';
        feedbackBox.textContent = '';
        nextBtn.hidden = true;

        const opts = [
            ['A', q.option_a],
            ['B', q.option_b],
            ['C', q.option_c],
            ['D', q.option_d],
        ];

        opts.forEach(([letter, text]) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'answer-btn';
            btn.dataset.letter = letter;
            btn.innerHTML = `<span class="answer-letter">${letter}</span><span class="answer-text"></span>`;
            btn.querySelector('.answer-text').textContent = text;
            btn.addEventListener('click', () => handleAnswer(letter));
            answerGrid.appendChild(btn);
        });

        updateHud();
        questionStartedAt = Date.now();
        startTimer();
    }

    function startTimer() {
        let remaining = SECONDS_PER_QUESTION;
        hudTimer.textContent = remaining;

        // Reset bar to 100% with no transition. Double-RAF ensures the browser
        // commits the reset before we install the long transition + final state,
        // otherwise the transition either skips or starts from a stale width.
        timerBarFill.style.transition = 'none';
        timerBarFill.style.width = '100%';
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                timerBarFill.style.transition = `width ${SECONDS_PER_QUESTION}s linear`;
                timerBarFill.style.width = '0%';
            });
        });

        timerInterval = setInterval(() => {
            remaining -= 1;
            hudTimer.textContent = Math.max(0, remaining);
            if (remaining <= 5) hudTimer.classList.add('hud-timer--urgent');
            if (remaining <= 0) {
                clearTimer();
                handleAnswer(null); // timed out
            }
        }, 1000);
    }

    function clearTimer() {
        if (timerInterval) {
            clearInterval(timerInterval);
            timerInterval = null;
        }
        hudTimer.classList.remove('hud-timer--urgent');
        // Freeze the bar at its current visual width — the CSS transition
        // otherwise keeps draining even after the JS timer stops.
        const frozenWidth = getComputedStyle(timerBarFill).width;
        timerBarFill.style.transition = 'none';
        timerBarFill.style.width = frozenWidth;
    }

    function handleAnswer(letter) {
        clearTimer();
        const q = questions[currentIndex];
        if (letter !== null) {
            answers[q.id] = letter;
        }

        // Visual: highlight selected and indicate correctness vs (we don't know correct
        // until server tells us, but we can mark selected + lock buttons; we reveal
        // correctness post-grade from the server). For an instant feel, we use a local
        // optimistic UI: lock buttons + show waiting state, then color after submit.
        // Simpler approach: do all grading at the end (server-side). We just lock + advance.
        const buttons = answerGrid.querySelectorAll('.answer-btn');
        buttons.forEach((b) => {
            b.disabled = true;
            if (letter !== null && b.dataset.letter === letter) {
                b.classList.add('answer-btn--selected');
            }
        });

        // Live feedback (assumes nothing about correct answer yet — we'll learn at submit)
        if (letter === null) {
            feedbackBox.hidden = false;
            feedbackBox.classList.add('question-feedback--miss');
            feedbackBox.textContent = t('js.timeUp');
            loseLife();
        } else {
            feedbackBox.hidden = false;
            feedbackBox.classList.add('question-feedback--locked');
            feedbackBox.textContent = t('js.lockedIn', letter);
        }

        nextBtn.hidden = false;
        nextBtn.focus();
    }

    function loseLife() {
        livesRemaining = Math.max(0, livesRemaining - 1);
        updateHud();
    }

    nextBtn.addEventListener('click', () => {
        if (livesRemaining <= 0) {
            submitQuiz();
            return;
        }
        if (currentIndex >= questions.length - 1) {
            submitQuiz();
            return;
        }
        currentIndex += 1;
        renderQuestion();
    });

    function updateHud() {
        hudProgress.textContent = `${currentIndex + 1} / ${questions.length || 5}`;
        hudLives.innerHTML = '';
        for (let i = 0; i < MAX_LIVES; i++) {
            const span = document.createElement('span');
            span.className = 'life' + (i >= livesRemaining ? ' life--lost' : '');
            span.textContent = '♥';
            hudLives.appendChild(span);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Submit + Results
    // ─────────────────────────────────────────────────────────────
    async function submitQuiz() {
        clearTimer();
        nextBtn.disabled = true;
        nextBtn.textContent = t('js.scoring');

        const timeTaken = Math.round((Date.now() - quizStartedAt) / 1000);

        try {
            const resp = await fetch('api/submit.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ answers, time_taken: timeTaken }),
            });
            const data = await resp.json();

            if (!resp.ok || data.error) {
                throw new Error(data.error || t('js.couldNotLoad'));
            }

            showResults(data);
        } catch (err) {
            feedbackBox.hidden = false;
            feedbackBox.classList.add('question-feedback--miss');
            feedbackBox.textContent = t('js.saveError') + (err.message || 'Network error.');
            nextBtn.disabled = false;
            nextBtn.textContent = t('js.retrySubmit');
        }
    }

    function showResults(data) {
        playStage.hidden = true;
        resultsStage.hidden = false;
        resultsStage.scrollIntoView({ behavior: 'smooth', block: 'start' });

        const total = data.total_questions;
        const correct = data.correct_count;
        const score = data.score;
        const accuracy = total ? Math.round((correct / total) * 100) : 0;

        resultsScore.textContent = score;
        resultsCorrect.textContent = `${correct} / ${total}`;
        resultsAccuracy.textContent = accuracy + '%';
        resultsTime.textContent = data.time_taken + 's';

        // Animate score ring
        const circumference = 2 * Math.PI * 52;
        resultsRing.style.strokeDasharray = circumference;
        resultsRing.style.strokeDashoffset = circumference;
        requestAnimationFrame(() => {
            resultsRing.style.strokeDashoffset = circumference * (1 - accuracy / 100);
        });

        // Eyebrow / title flavor based on performance
        if (data.perfect) {
            resultsEyebrow.textContent = t('js.perfectRun');
            resultsTitle.textContent = t('js.perfectTitle', heroName);
        } else if (accuracy >= 80) {
            resultsEyebrow.textContent = t('js.outstanding');
            resultsTitle.textContent = t('js.expertTitle', heroName);
        } else if (accuracy >= 50) {
            resultsEyebrow.textContent = t('js.solidEffort');
            resultsTitle.textContent = t('js.solidTitle', heroName);
        } else {
            resultsEyebrow.textContent = t('js.keepStudying');
            resultsTitle.textContent = t('js.studyTitle', heroName);
        }

        if (data.saved) {
            resultsSaved.textContent = t('js.saved');
            resultsSaved.className = 'results-saved results-saved--ok';
        } else if (!isLoggedIn) {
            resultsSaved.innerHTML = t('js.notSavedGuest');
            resultsSaved.className = 'results-saved results-saved--warn';
        } else {
            resultsSaved.textContent = '';
        }

        // Breakdown
        resultsBreakdown.innerHTML = '<h3 class="breakdown-title">' + escapeHtml(t('js.review')) + '</h3>';
        const list = document.createElement('ol');
        list.className = 'breakdown-list';
        data.results.forEach((r, i) => {
            const li = document.createElement('li');
            li.className = 'breakdown-item ' + (r.is_correct ? 'breakdown-item--correct' : 'breakdown-item--wrong');

            const status = document.createElement('span');
            status.className = 'breakdown-status';
            status.textContent = r.is_correct ? '✓' : '✕';

            const body = document.createElement('div');
            body.className = 'breakdown-body';

            const q = document.createElement('div');
            q.className = 'breakdown-question';
            q.textContent = (i + 1) + '. ' + r.question_text;

            const correctText = r.options[r.correct_option];
            const meta = document.createElement('div');
            meta.className = 'breakdown-meta';

            if (r.selected === null) {
                meta.innerHTML =
                    '<span class="breakdown-yours">' + escapeHtml(t('js.noAnswer')) + '</span> &middot; ' +
                    '<span class="breakdown-correct">' + escapeHtml(t('js.correctIs', r.correct_option, correctText)) + '</span>';
            } else if (r.is_correct) {
                meta.innerHTML =
                    '<span class="breakdown-correct">' + escapeHtml(t('js.youChose', r.selected, r.options[r.selected])) + '</span>';
            } else {
                meta.innerHTML =
                    '<span class="breakdown-yours">' + escapeHtml(t('js.yourAnswer', r.selected, r.options[r.selected])) + '</span> &middot; ' +
                    '<span class="breakdown-correct">' + escapeHtml(t('js.correctIs', r.correct_option, correctText)) + '</span>';
            }

            body.appendChild(q);
            body.appendChild(meta);
            li.appendChild(status);
            li.appendChild(body);
            list.appendChild(li);
        });
        resultsBreakdown.appendChild(list);
    }

    // ─────────────────────────────────────────────────────────────
    // Play again
    // ─────────────────────────────────────────────────────────────
    playAgainBtn.addEventListener('click', () => {
        resultsStage.hidden = true;
        setupStage.hidden = false;
        selectedDifficulty = null;
        difficultyCards.forEach((c) => {
            c.classList.remove('selected');
            c.setAttribute('aria-checked', 'false');
        });
        startBtn.disabled = true;
        startBtn.textContent = t('js.startQuiz');
        setupHint.textContent = t('js.selectToBegin');
        setupHint.classList.remove('setup-hint--error');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // ─────────────────────────────────────────────────────────────
    // Utils
    // ─────────────────────────────────────────────────────────────
    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value == null ? '' : String(value);
        return div.innerHTML;
    }
}());
