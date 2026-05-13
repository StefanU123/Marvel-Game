document.addEventListener('DOMContentLoaded', () => {
    const game = document.getElementById('game');
    const difficultySelect = document.getElementById('difficulty');
    const startButton = document.getElementById('start-quiz');
    const questionArea = document.getElementById('question-area');

    if (!game || !difficultySelect || !startButton || !questionArea) {
        return;
    }

    startButton.addEventListener('click', async () => {
        const heroId = game.dataset.heroId;
        const difficulty = difficultySelect.value;
        const url = `/api/question.php?hero_id=${encodeURIComponent(heroId)}&difficulty=${encodeURIComponent(difficulty)}`;

        questionArea.innerHTML = '<p class="message">Loading question...</p>';

        try {
            const response = await fetch(url);
            const data = await response.json();

            if (!response.ok || data.error) {
                questionArea.innerHTML = `<p class="message">${escapeHtml(data.error || 'Could not load question.')}</p>`;
                return;
            }

            renderQuestion(data);
        } catch (error) {
            questionArea.innerHTML = '<p class="message">Could not load question.</p>';
        }
    });

    function renderQuestion(question) {
        const options = [
            ['A', question.option_a],
            ['B', question.option_b],
            ['C', question.option_c],
            ['D', question.option_d],
        ];

        questionArea.innerHTML = '';

        const title = document.createElement('h2');
        title.textContent = question.question_text;
        questionArea.appendChild(title);

        const answerList = document.createElement('div');
        answerList.className = 'answer-list';

        options.forEach(([letter, text]) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'button answer-button';
            button.textContent = `${letter}. ${text}`;
            button.addEventListener('click', () => {
                console.log('Selected option:', letter, 'Question id:', question.id);
            });
            answerList.appendChild(button);
        });

        questionArea.appendChild(answerList);
    }

    function escapeHtml(value) {
        const element = document.createElement('div');
        element.textContent = value;
        return element.innerHTML;
    }
});
