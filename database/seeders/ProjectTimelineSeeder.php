<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Prasso\ProjectManagement\Models\Project;
use Prasso\ProjectManagement\Models\Task;
use Carbon\Carbon;

class ProjectTimelineSeeder extends Seeder
{
    public function run()
    {
        $project = Project::create([
            'name' => 'AutoPro Hub Development',
            'description' => 'Development of the AutoPro Hub platform with core website functionality, booking system, and advanced features.',
            'status' => 'in_progress',
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addMonths(9),
            'user_id' => 1, // Assuming user ID 1 exists
        ]);

        // Phase 1: Foundation
        $this->createPhase1Tasks($project);
        
        // Phase 2: Growth Tools
        $this->createPhase2Tasks($project);
        
        // Phase 3: Advanced Features
        $this->createPhase3Tasks($project);
    }

    private function createPhase1Tasks($project)
    {
        $startDate = Carbon::now();
        
        // Core Website Functionality
        $tasks = [
            'Custom domain setup and management',
            'Responsive website templates',
            'Service listing pages',
            'Photo gallery system',
            'Contact forms',
            'Staff profile pages',
            'Business hours display',
            'Location map integration',
            'SSL security implementation',
        ];

        foreach ($tasks as $index => $task) {
            Task::create([
                'project_id' => $project->id,
                'name' => $task,
                'phase' => 'Phase 1',
                'status' => 'pending',
                'order' => $index,
                'start_date' => $startDate,
                'due_date' => $startDate->copy()->addDays(30),
            ]);
        }

        // Booking System
        $startDate = $startDate->copy()->addMonth();
        $bookingTasks = [
            'Real-time availability calendar',
            'Service type selection',
            'Duration-based scheduling',
            'Automatic conflict prevention',
            'Customer vehicle information storage',
            'Email/SMS confirmation system',
            'Cancellation/rescheduling tools',
            'Recurring appointment setup',
            'Waiting list management',
        ];

        foreach ($bookingTasks as $index => $task) {
            Task::create([
                'project_id' => $project->id,
                'name' => $task,
                'phase' => 'Phase 1',
                'status' => 'pending',
                'order' => $index + 10,
                'start_date' => $startDate,
                'due_date' => $startDate->copy()->addDays(30),
            ]);
        }

        // Customer Management
        $startDate = $startDate->copy()->addMonth();
        $customerTasks = [
            'Customer profile creation',
            'Vehicle history records',
            'Service history tracking',
            'Document storage',
            'Communication log',
            'Reminder system',
        ];

        foreach ($customerTasks as $index => $task) {
            Task::create([
                'project_id' => $project->id,
                'name' => $task,
                'phase' => 'Phase 1',
                'status' => 'pending',
                'order' => $index + 20,
                'start_date' => $startDate,
                'due_date' => $startDate->copy()->addDays(30),
            ]);
        }
    }

    private function createPhase2Tasks($project)
    {
        $startDate = Carbon::now()->addMonths(3);
        
        // Marketing Tools
        $tasks = [
            'Email marketing integration',
            'Social media management',
            'Review collection system',
            'SEO optimization tools',
            'Content management system',
            'Analytics dashboard',
            'Customer feedback surveys',
            'Promotional campaign tools',
            'Loyalty program management',
        ];

        foreach ($tasks as $index => $task) {
            Task::create([
                'project_id' => $project->id,
                'name' => $task,
                'phase' => 'Phase 2',
                'status' => 'pending',
                'order' => $index + 30,
                'start_date' => $startDate,
                'due_date' => $startDate->copy()->addDays(30),
            ]);
        }

        // Mobile App Development
        $startDate = $startDate->copy()->addMonths(2);
        $appTasks = [
            'iOS and Android apps',
            'Push notification system',
            'Mobile booking interface',
            'Service status tracking',
            'Digital inspection reports',
            'Mobile payment processing',
            'Photo/document upload',
            'Technician mobile interface',
            'Emergency service requests',
        ];

        foreach ($appTasks as $index => $task) {
            Task::create([
                'project_id' => $project->id,
                'name' => $task,
                'phase' => 'Phase 2',
                'status' => 'pending',
                'order' => $index + 40,
                'start_date' => $startDate,
                'due_date' => $startDate->copy()->addDays(30),
            ]);
        }
    }

    private function createPhase3Tasks($project)
    {
        $startDate = Carbon::now()->addMonths(6);
        
        // Advanced Features
        $tasks = [
            'Inventory management',
            'Parts ordering system',
            'Employee scheduling',
            'Time clock integration',
            'Commission tracking',
            'Multi-location support',
            'Fleet management tools',
            'Digital vehicle inspections',
            'Service package builder',
        ];

        foreach ($tasks as $index => $task) {
            Task::create([
                'project_id' => $project->id,
                'name' => $task,
                'phase' => 'Phase 3',
                'status' => 'pending',
                'order' => $index + 50,
                'start_date' => $startDate,
                'due_date' => $startDate->copy()->addDays(30),
            ]);
        }

        // Integration Capabilities
        $startDate = $startDate->copy()->addMonth();
        $integrationTasks = [
            'QuickBooks integration',
            'Payment gateway connections',
            'Parts supplier APIs',
            'Vehicle diagnostic tool integration',
            'Insurance company portals',
            'Fleet management systems',
            'Text message services',
            'Calendar sync (Google, Outlook)',
            'Document scanning integration',
        ];

        foreach ($integrationTasks as $index => $task) {
            Task::create([
                'project_id' => $project->id,
                'name' => $task,
                'phase' => 'Phase 3',
                'status' => 'pending',
                'order' => $index + 60,
                'start_date' => $startDate,
                'due_date' => $startDate->copy()->addDays(30),
            ]);
        }
    }
}
