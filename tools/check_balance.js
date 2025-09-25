const fs = require('fs');
const path = require('path');

function checkBalance(content, filename) {
    let braces = 0;
    let parens = 0;
    let brackets = 0;
    let backticks = 0;
    let inString = false;
    let stringChar = '';
    
    for (let i = 0; i < content.length; i++) {
        const char = content[i];
        const prevChar = i > 0 ? content[i - 1] : '';
        
        // Handle string literals
        if ((char === '"' || char === "'" || char === '`') && prevChar !== '\\') {
            if (!inString) {
                inString = true;
                stringChar = char;
            } else if (char === stringChar) {
                inString = false;
                stringChar = '';
            }
        }
        
        if (!inString) {
            switch (char) {
                case '{': braces++; break;
                case '}': braces--; break;
                case '(': parens++; break;
                case ')': parens--; break;
                case '[': brackets++; break;
                case ']': brackets--; break;
                case '`': backticks++; break;
            }
        }
    }
    
    console.log(`\n=== ${filename} ===`);
    console.log(`Braces: ${braces === 0 ? '✓ Balanced' : '✗ Unbalanced (' + braces + ')'}`);
    console.log(`Parentheses: ${parens === 0 ? '✓ Balanced' : '✗ Unbalanced (' + parens + ')'}`);
    console.log(`Brackets: ${brackets === 0 ? '✓ Balanced' : '✗ Unbalanced (' + brackets + ')'}`);
    console.log(`Backticks: ${backticks % 2 === 0 ? '✓ Even' : '✗ Odd (' + backticks + ')'}`);
    
    return {
        balanced: braces === 0 && parens === 0 && brackets === 0 && backticks % 2 === 0,
        braces, parens, brackets, backticks
    };
}

// Check waitlist.php
try {
    const waitlistPath = 'app/Views/admin/appointments/waitlist.php';
    const waitlistContent = fs.readFileSync(waitlistPath, 'utf8');
    const waitlistResult = checkBalance(waitlistContent, 'waitlist.php');
    
    // Check calendar scripts
    const calendarPath = 'app/Views/templates/calendar/scripts.php';
    if (fs.existsSync(calendarPath)) {
        const calendarContent = fs.readFileSync(calendarPath, 'utf8');
        const calendarResult = checkBalance(calendarContent, 'calendar/scripts.php');
    }
    
    console.log('\n=== Summary ===');
    console.log(`Waitlist: ${waitlistResult.balanced ? '✓ All balanced' : '✗ Issues found'}`);
    
} catch (error) {
    console.error('Error checking files:', error.message);
}