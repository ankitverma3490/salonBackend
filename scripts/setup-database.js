// Database Setup Script for Salon Management System
// Run this with: node setup-database.js

import { createClient } from '@supabase/supabase-js';
import fs from 'fs';

// Read environment variables
const supabaseUrl = process.env.VITE_SUPABASE_URL || 'your-supabase-url';
const supabaseServiceKey = process.env.SUPABASE_SERVICE_ROLE_KEY || 'your-service-role-key';

const supabase = createClient(supabaseUrl, supabaseServiceKey);

async function setupDatabase() {
  try {
    console.log('Setting up database...');
    
    // Read SQL file
    const sqlScript = fs.readFileSync('database-setup.sql', 'utf8');
    
    // Execute SQL
    const { data, error } = await supabase.rpc('exec_sql', { sql: sqlScript });
    
    if (error) {
      console.error('Error setting up database:', error);
      
      // Try individual commands
      console.log('Trying individual setup commands...');
      
      // Add columns to profiles table
      const { error: alterError } = await supabase
        .from('profiles')
        .select('user_type')
        .limit(1);
      
      if (alterError && alterError.message.includes('column "user_type" does not exist')) {
        console.log('Adding user_type column...');
        // This would need to be done via Supabase dashboard SQL editor
        console.log('Please run the SQL commands in database-setup.sql via Supabase dashboard');
      }
    } else {
      console.log('Database setup completed successfully!');
    }
    
  } catch (error) {
    console.error('Setup failed:', error);
  }
}

setupDatabase();