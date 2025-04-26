// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    
    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    
    // Loop over them and prevent submission
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
    });
    
    // Quiz options selection
    const quizOptions = document.querySelectorAll('.quiz-option');
    
    quizOptions.forEach(option => {
        option.addEventListener('click', function() {
            // Remove selected class from all options in the same question
            const questionId = this.dataset.questionId;
            const questionOptions = document.querySelectorAll(`.quiz-option[data-question-id="${questionId}"]`);
            
            questionOptions.forEach(opt => {
                opt.classList.remove('selected');
                opt.querySelector('input[type="radio"]').checked = false;
            });
            
            // Add selected class to clicked option
            this.classList.add('selected');
            this.querySelector('input[type="radio"]').checked = true;
        });
    });
    
    // Profile image preview
    const profileImageInput = document.getElementById('profile_image');
    const profileImagePreview = document.getElementById('profile_image_preview');
    
    if (profileImageInput && profileImagePreview) {
        profileImageInput.addEventListener('change', function() {
            const file = this.files[0];
            
            if (file) {
                const reader = new FileReader();
                
                reader.addEventListener('load', function() {
                    profileImagePreview.src = this.result;
                });
                
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Timer for quiz
    const quizTimer = document.getElementById('quiz-timer');
    
    if (quizTimer) {
        let timeLeft = parseInt(quizTimer.dataset.timeLimit) * 60; // Convert minutes to seconds
        
        const updateTimer = () => {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            
            quizTimer.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            if (timeLeft <= 0) {
                // Auto-submit the quiz when timer ends
                document.getElementById('quiz-form').submit();
            } else {
                timeLeft--;
                setTimeout(updateTimer, 1000);
            }
        };
        
        updateTimer();
    }
    
    // Search functionality
    const searchInput = document.getElementById('search');
    
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('search-form').submit();
            }
        });
    }
    
    // Password visibility toggle
    const togglePasswordButtons = document.querySelectorAll('.toggle-password');
    
    togglePasswordButtons.forEach(button => {
        button.addEventListener('click', function() {
            const passwordInput = document.querySelector(this.dataset.target);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                this.innerHTML = '<i class="fas fa-eye-slash"></i>';
            } else {
                passwordInput.type = 'password';
                this.innerHTML = '<i class="fas fa-eye"></i>';
            }
        });
    });
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
});
