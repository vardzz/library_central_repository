STEP 1: REPOSITORY CREATION
• Create a repository in GitHub named library_central_repository.
• This will serve as the central repository where all project files are stored, tracked, and
collaboratively developed.
STEP 2: ADDING COLLABORATORS
• Invited group members as collaborators to the repository.
• This gives each member the ability to clone, pull, push, and manage their own tasks
within the project.
STEP 3: BRANCH CREATION
• created multiple branches aside from the default main branch.
• Branching Name Convention: core-task/surname-assigned-task
Branches Created:
- core-task/sevilla-borrow-return
- core-task/varde-search
- core-task/reyes-browse-view
- core-task/venancio-edit-remove
- core-task/rosal-add
Steps in Git Bash to Create a Branch:
- git checkout main
- git checkout -b core-task/surname-assigned-task (new branch to add)
- git push -u origin core-task/surname-assigned-task (
STEP 4: DOCKER CONTAINER SETUP
• Created a Docker container named library_central_repository.
• To run the project in an isolated environment, ensuring consistent setup for all team
members.
Command Used:
• docker-compose up -d –build
STEP 5: PROJECT FOLDER AND FILE STRUCTURE
• Inside the repository folder library_central_repository, the following structure was
created:
Purpose:
• sql/migrations.sql → Database schema and table definitions.
• src/ → Main application source code (PHP files, styles, Dockerfile).
• .env.example → Environment variables template.
• .gitignore → Ignored files in Git.
• docker-compose.yml → Container configuration.
• README.md → Project description and instructions.
STEP 6: ADDING FILES TO THE MAIN BRANCH
• After creating the initial project structure, files were added to the main branch using Git
Bash.
Commands Used:
- git checkout main
- git status
- git add .
- git commit -m "Initial project structure"
- git push origin main (from local to remote)
STEP 7: PULLING FILES FROM MAIN INTO RESPECTIVE BRANCHES
• Each member pulled the latest files from the main branch into their assigned branch to
have a synchronized copy.
• This ensures every branch has the updated project files from main before members start
working on their assigned tasks.
Commands Used:
- git checkout core-task/surname-assigned-task
- git pull origin main (from remote to local)
STEP 8: MEMBERS WORK ON ASSIGNED TASKS
• Each member worked on their assigned task in their respective branch.
Example Git Bash command in adding a new file in their respective branch:
- git checkout core-task/varde-search
- git status
- git add .
- git commit -m "new file added: search.php"
- git push origin core-task/varde-search
STEP 9: CONTINUOUS PUSH AND PULL UNTIL PROJECT COMPLETION
• Throughout development, members repeatedly used push and pull commands to sync
updates with GitHub and their own branches.
• Branches are merged into main
Merge Command:
git merge core-task/surname-assigned-task
STEP 10: MERGE REQUEST
• When all files are ready to be merged to the main branch, collaborators create a pull
request, at which point reviewers can either comment, approve, or suggest changes on
that specific branch.
• Once approved, the branch will then be merged to the main.
