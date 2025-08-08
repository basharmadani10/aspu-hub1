from flask import Flask, request, jsonify
from huggingface_hub import InferenceClient
from dotenv import load_dotenv
from flask_cors import CORS  # <-- Add this import

import os, textwrap


load_dotenv()
key = os.getenv('API_Keys')
if not key:
    raise ValueError("API_Keys not found. Please set it via environment variables.")


client = InferenceClient(
    provider="fireworks-ai",
    api_key="fw_3ZUjTJ64yeaH7c4Bm94Q8FzA",
)


app = Flask(__name__)
CORS(app)


subjects = {
    "Introduction to Programming in C++": 1
}
def get_system_prompt(subject: str) -> str:
    """
    Generate a specialized system prompt that instructs the chat model to create high-quality, subject-specific exam questions tailored for university computer science students.
    """
    raw = f"""
    You are an intelligent assistant and an expert in teaching the {subject}. Your task is to create effective and appropriate exam questions for university computer science students. While creating the questions, follow these guidelines:

    1. Focus on fundamental and core concepts within the designated learning material.
    2. Diversify the question types: code output prediction, multiple choice, and code error correction.
    3. Produce exactly three questions in total—one for each of the following types: code output prediction, multiple choice, and code correction.
    4. Ensure the questions address concepts that students often struggle with, including edge cases and common mistakes.
    5. Design questions with a gradual difficulty level, ranging from basic to intermediate, and make sure the wording is clear and understandable.
    6. Use realistic examples from practical programming contexts; avoid purely theoretical examples.
    7. Each question must include:
       - A clearly written prompt
       - For multiple choice: a list of answer options
    8. Do **not** include answer keys, correct answers, or explanations—only present the questions themselves.
    9. Format the output in Markdown, using a level‑3 heading (`###`) for each question title, followed by the prompt and any code block or options.
    10. Format the questions with clear structure and appropriate indentation to enhance readability.
    11. Avoid redundancy and ambiguity in the questions.

    Your goal is to help students accurately assess their understanding of the subject {subject} through educational, thought‑provoking, and high‑quality questions.
    """
    return textwrap.dedent(raw).strip()


examples = [
    {
        "subject": "Introduction to Programming in C++",
        "subject_content": """
### Core Concepts:

1. **Variables and Data Types**
   - Declaration and initialization
   - Arithmetic operations (`++`, `--`, `+=`)
   - Variable scope

2. **Conditional Structures**
   - Nested if-else statements
   - switch-case with break usage

3. **Loops**
   - for loops with multiple counters
   - Difference between while and do-while
   - Controlling loops using break and continue

4. **Arrays**
   - One-dimensional and two-dimensional arrays
   - Accessing elements
   - Nested initialization
"""
    }
]


def get_user_prompt(subject: str) -> str:
    """
    Construct a user-facing instruction that directs the chat model to generate three varied assessment questions
    tailored to the given subject content,
    including guidelines on difficulty progression, edge-case coverage, and clear code formatting.
    """
    if subject != 'Introduction to Programming in C++':
        raise ValueError(f"No user prompt defined for subject '{subject}'")

    raw = f"""
    You are an expert assistant in evaluating the proficiency of university computer science students in the {subject}.

    Based on the following educational content:
    {examples[0]['subject_content']}

    Create exactly **three assessment questions**, one for each of the following types:
    1. Output Prediction
    2. Multiple Choice
    3. Code Correction

    **Important guidelines for question creation:**
    1. Focus on concepts that are typically difficult or confusing for students.
    2. Try to include edge cases where appropriate.
    3. Use clean and well-indented code formatting.
    4. Use realistic examples from practical programming scenarios.
    5. “Start the response immediately with the first question. Do not include phrases like ‘Here are…’, ‘In this assessment…’, or similar.”

    **Each question must include the following format:**
    - A short title (e.g., "Question 1: Output Prediction")
    - A clearly written prompt
    - A code block (if applicable)
    - Options (for multiple choice questions)

    **Additional instructions:**
    1. Do **not** include any answer keys, correct answers, or explanations—only present the questions themselves.
    2. Format the output in Markdown, using level-3 headings (`###`) for each question title and corresponding sections.

    **Example output structure:**
    ### Question 1: Output Prediction
    ```cpp
    // sample code here
    ```
    *(No answer key or explanation)*

    ### Question 2: Multiple Choice
    ```cpp
    // sample code here
    ```
    A. Option 1
    B. Option 2
    C. Option 3
    D. Option 4

    ### Question 3: Code Correction
    ```cpp
    // code with an error here
    ```
    """
    return textwrap.dedent(raw).strip()


def generate_exam_questions(subject: str) -> str:
    if subject not in subjects:
        raise ValueError(f"Subject '{subject}' is not supported.")

    system_msg = get_system_prompt(subject)
    user_msg = get_user_prompt(subject)

    try:
        response = client.chat.completions.create(
            model="deepseek-ai/DeepSeek-V3",
            messages=[
                {"role": "system", "content": system_msg},
                {"role": "user", "content": user_msg}
            ],
            max_tokens=1024,
            temperature=1,
            top_p=0.95
        )
        return response.choices[0].message.content
    except Exception as e:
        return f"An error occurred while generating the questions: {str(e)}"

# API endpoint
@app.route('/generate-questions', methods=['POST'])
def generate_questions_api():
    try:
        data = request.json
        subject = data.get('subject')
        if not subject:
            return jsonify({'error': 'Missing subject'}), 400

        questions = generate_exam_questions(subject)
        return jsonify({'subject': subject, 'questions': questions})

    except Exception as e:
        return jsonify({'error': str(e)}), 500

# لتشغيل السيرفر محلياً
if __name__ == '__main__':
    app.run(debug=True, port=9001)
