-- PostgreSQL schema for Exam Reviewer

-- Users table
CREATE TABLE IF NOT EXISTS users (
    user_id SERIAL PRIMARY KEY,
    username VARCHAR(30) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    profile_image VARCHAR(255) DEFAULT NULL,
    role VARCHAR(5) NOT NULL DEFAULT 'user' CHECK (role IN ('user', 'admin')),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP DEFAULT NULL
);

-- Subjects table
CREATE TABLE IF NOT EXISTS subjects (
    subject_id SERIAL PRIMARY KEY,
    subject_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT DEFAULT NULL
);

-- Questions table
CREATE TABLE IF NOT EXISTS questions (
    question_id SERIAL PRIMARY KEY,
    subject_id INTEGER NOT NULL,
    question_text TEXT NOT NULL,
    option_a TEXT NOT NULL,
    option_b TEXT NOT NULL,
    option_c TEXT NOT NULL,
    option_d TEXT NOT NULL,
    correct_answer CHAR(1) NOT NULL CHECK (correct_answer IN ('A', 'B', 'C', 'D')),
    explanation TEXT DEFAULT NULL,
    FOREIGN KEY (subject_id) REFERENCES subjects (subject_id)
);

-- Quiz results table
CREATE TABLE IF NOT EXISTS quiz_results (
    result_id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    subject_id INTEGER NOT NULL,
    score INTEGER NOT NULL,
    total_questions INTEGER NOT NULL,
    date_taken TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users (user_id),
    FOREIGN KEY (subject_id) REFERENCES subjects (subject_id)
);

-- Create indexes
CREATE INDEX idx_question_subject ON questions (subject_id);
CREATE INDEX idx_result_user ON quiz_results (user_id);
CREATE INDEX idx_result_subject ON quiz_results (subject_id);

-- Insert default admin user
-- Username: admin, Password: admin123 (hashed)
INSERT INTO users (username, email, password, role, created_at) 
VALUES ('admin', 'admin@example.com', '$2y$10$P58xzWm6UXs33QKnKPg80uUrHUvBaZlO5xVLQ1rIHEDYrklzhPmPi', 'admin', NOW())
ON CONFLICT (username) DO NOTHING;

-- Insert sample subjects
INSERT INTO subjects (subject_name, description) 
VALUES 
('Mathematics', 'Questions related to algebra, geometry, calculus, and other mathematical concepts'),
('Science', 'Questions covering physics, chemistry, biology, and general science'),
('History', 'Questions about world history, historical events, and important figures'),
('Computer Science', 'Questions related to programming, algorithms, data structures, and computing concepts'),
('English', 'Questions about grammar, vocabulary, literature, and language skills')
ON CONFLICT (subject_name) DO NOTHING;

-- Insert sample questions for Mathematics
INSERT INTO questions (subject_id, question_text, option_a, option_b, option_c, option_d, correct_answer, explanation) 
SELECT 
    s.subject_id,
    'What is the value of π (pi) to two decimal places?',
    '3.14', '3.16', '3.12', '3.18',
    'A',
    'Pi (π) is approximately equal to 3.14159..., which rounds to 3.14 when expressed to two decimal places.'
FROM subjects s WHERE s.subject_name = 'Mathematics'
LIMIT 1;

INSERT INTO questions (subject_id, question_text, option_a, option_b, option_c, option_d, correct_answer, explanation) 
SELECT 
    s.subject_id,
    'If a triangle has angles measuring 60°, 60°, and 60°, what type of triangle is it?',
    'Scalene', 'Isosceles', 'Equilateral', 'Right-angled',
    'C',
    'An equilateral triangle has three equal sides and three equal angles, each measuring 60 degrees.'
FROM subjects s WHERE s.subject_name = 'Mathematics'
LIMIT 1;

INSERT INTO questions (subject_id, question_text, option_a, option_b, option_c, option_d, correct_answer, explanation) 
SELECT 
    s.subject_id,
    'What is the square root of 144?',
    '14', '12', '16', '10',
    'B',
    'The square root of 144 is 12, because 12 × 12 = 144.'
FROM subjects s WHERE s.subject_name = 'Mathematics'
LIMIT 1;

-- Insert sample questions for Science
INSERT INTO questions (subject_id, question_text, option_a, option_b, option_c, option_d, correct_answer, explanation) 
SELECT 
    s.subject_id,
    'What is the chemical symbol for gold?',
    'Go', 'Au', 'Ag', 'Gd',
    'B',
    'The chemical symbol for gold is Au, derived from the Latin word "aurum."'
FROM subjects s WHERE s.subject_name = 'Science'
LIMIT 1;

INSERT INTO questions (subject_id, question_text, option_a, option_b, option_c, option_d, correct_answer, explanation) 
SELECT 
    s.subject_id,
    'Which of the following is NOT a state of matter?',
    'Plasma', 'Gas', 'Energy', 'Solid',
    'C',
    'The four states of matter are solid, liquid, gas, and plasma. Energy is a form of power, not a state of matter.'
FROM subjects s WHERE s.subject_name = 'Science'
LIMIT 1;

INSERT INTO questions (subject_id, question_text, option_a, option_b, option_c, option_d, correct_answer, explanation) 
SELECT 
    s.subject_id,
    'What is the largest organ in the human body?',
    'Heart', 'Liver', 'Skin', 'Brain',
    'C',
    'The skin is the largest organ in the human body, covering an area of about 2 square meters in adults.'
FROM subjects s WHERE s.subject_name = 'Science'
LIMIT 1;

-- Insert sample questions for History
INSERT INTO questions (subject_id, question_text, option_a, option_b, option_c, option_d, correct_answer, explanation) 
SELECT 
    s.subject_id,
    'In which year did World War II end?',
    '1943', '1944', '1945', '1946',
    'C',
    'World War II ended in 1945 with the surrender of Germany in May and Japan in September.'
FROM subjects s WHERE s.subject_name = 'History'
LIMIT 1;

INSERT INTO questions (subject_id, question_text, option_a, option_b, option_c, option_d, correct_answer, explanation) 
SELECT 
    s.subject_id,
    'Who was the first President of the United States?',
    'Thomas Jefferson', 'John Adams', 'Benjamin Franklin', 'George Washington',
    'D',
    'George Washington was the first President of the United States, serving from 1789 to 1797.'
FROM subjects s WHERE s.subject_name = 'History'
LIMIT 1;

INSERT INTO questions (subject_id, question_text, option_a, option_b, option_c, option_d, correct_answer, explanation) 
SELECT 
    s.subject_id,
    'The French Revolution began in which year?',
    '1789', '1799', '1769', '1779',
    'A',
    'The French Revolution began in 1789 with the storming of the Bastille on July 14.'
FROM subjects s WHERE s.subject_name = 'History'
LIMIT 1;

-- Insert sample questions for Computer Science
INSERT INTO questions (subject_id, question_text, option_a, option_b, option_c, option_d, correct_answer, explanation) 
SELECT 
    s.subject_id,
    'What does CPU stand for?',
    'Central Processing Unit', 'Computer Personal Unit', 'Central Process Utility', 'Central Processor Utility',
    'A',
    'CPU stands for Central Processing Unit, which is the main component of a computer that performs most of the processing.'
FROM subjects s WHERE s.subject_name = 'Computer Science'
LIMIT 1;

INSERT INTO questions (subject_id, question_text, option_a, option_b, option_c, option_d, correct_answer, explanation) 
SELECT 
    s.subject_id,
    'Which programming language is known as the "mother of all languages"?',
    'Java', 'C', 'Python', 'FORTRAN',
    'B',
    'C is often referred to as the "mother of all languages" because many modern programming languages have derived concepts and syntax from it.'
FROM subjects s WHERE s.subject_name = 'Computer Science'
LIMIT 1;

INSERT INTO questions (subject_id, question_text, option_a, option_b, option_c, option_d, correct_answer, explanation) 
SELECT 
    s.subject_id,
    'What does HTML stand for?',
    'Hyper Text Markup Language', 'High Tech Multi Language', 'Hyper Transfer Markup Language', 'High Text Machine Language',
    'A',
    'HTML stands for Hyper Text Markup Language, which is the standard markup language for creating web pages.'
FROM subjects s WHERE s.subject_name = 'Computer Science'
LIMIT 1;

-- Insert sample questions for English
INSERT INTO questions (subject_id, question_text, option_a, option_b, option_c, option_d, correct_answer, explanation) 
SELECT 
    s.subject_id,
    'Which of the following is a proper noun?',
    'city', 'beautiful', 'Paris', 'run',
    'C',
    'Paris is a proper noun because it is the name of a specific city. Proper nouns are always capitalized.'
FROM subjects s WHERE s.subject_name = 'English'
LIMIT 1;

INSERT INTO questions (subject_id, question_text, option_a, option_b, option_c, option_d, correct_answer, explanation) 
SELECT 
    s.subject_id,
    'Which sentence contains a grammatical error?',
    'She doesn''t like ice cream.', 'They are going to the store.', 'He don''t want to go.', 'We were at the park yesterday.',
    'C',
    'The correct form should be "He doesn''t want to go." The third-person singular form requires "doesn''t" instead of "don''t".'
FROM subjects s WHERE s.subject_name = 'English'
LIMIT 1;

INSERT INTO questions (subject_id, question_text, option_a, option_b, option_c, option_d, correct_answer, explanation) 
SELECT 
    s.subject_id,
    'What is the past tense of the verb "to write"?',
    'Writed', 'Wrote', 'Written', 'Writing',
    'B',
    'The past tense of "write" is "wrote." "Written" is the past participle form.'
FROM subjects s WHERE s.subject_name = 'English'
LIMIT 1;