# Agent Log & Project Notes

This file tracks the key decisions, plans, and progress for the task of adding new UI components to the chat application.

## High-Level Goal
The main objective is to create a variety of new UI components for a chat application, similar to how weather or flight booking information is displayed. These components are relevant to ERP and delivery platforms.

## Key Decisions & Progress

1.  **Initial Components:** We started by creating three initial components:
    *   `ProjectTask` (`components/tasks/project-task.tsx`)
    *   `DealTask` (`components/tasks/deal-task.tsx`)
    *   `NoteCard` (`components/notes/note-card.tsx`)

2.  **Client-Side Showcase:** After encountering issues with reliability and AI token usage, we decided to switch from an AI-driven showcase to a purely client-side one. There are now dedicated buttons in the UI to display each component with mock data, without making any backend calls.

3.  **Clear History Feature:** A feature to clear the entire chat history for a user was added. This includes a backend API endpoint and a button in the chat history sidebar with a confirmation dialog.

## Current Plan: Personal Tasks, Reminders, and Disambiguation

After implementing the Task Stack and Right Plane, the user provided new direction. The current focus is on creating a new, fully-featured "Personal Task" system and improving the AI's interaction model.

**The currently approved plan is as follows:**

1.  **AI Task Disambiguation:** The AI will be taught to ask for clarification when a user's request to create a task is ambiguous. It will present a new UI with buttons ("Project Task", "CRM Task", "Personal Task") to let the user choose.
2.  **Personal Task Backend:** A new `personal_tasks` table will be added to the database to store tasks that are not linked to projects or deals. This includes a `reminder_at` column to support scheduled reminders. The backend logic (queries, API routes) will be created to manage these tasks.
3.  **Reminder Functionality:**
    - A new "Set Reminder" UI with quick-action buttons ("Today", "Tomorrow", "Next Week") will be shown after a personal task is created.
    - The application will show in-app toast notifications for any reminders that are due when the app is opened.
4.  **"Done" Button:** A "Done" button will be added to all task cards that appear in the Right Plane. Clicking it will remove the task from the list.
