// Script to create a super admin user
// Run this with: node create-super-admin.js

import { createClient } from '@supabase/supabase-js';
import dotenv from 'dotenv';

dotenv.config();

const supabaseUrl = process.env.VITE_SUPABASE_URL;
const supabaseServiceKey = process.env.SUPABASE_SERVICE_ROLE_KEY; // You need to add this to .env

if (!supabaseUrl || !supabaseServiceKey) {
  console.error('Missing Supabase credentials in .env file');
  console.log('Required variables:');
  console.log('- VITE_SUPABASE_URL');
  console.log('- SUPABASE_SERVICE_ROLE_KEY');
  process.exit(1);
}

const supabase = createClient(supabaseUrl, supabaseServiceKey);

async function createSuperAdmin() {
  try {
    // Create a new user
    const { data: authData, error: authError } = await supabase.auth.admin.createUser({
      email: 'admin@salon.com',
      password: 'admin123456',
      email_confirm: true,
      user_metadata: {
        full_name: 'Super Admin',
        user_type: 'admin'
      }
    });

    if (authError) {
      console.error('Error creating user:', authError);
      return;
    }

    console.log('✅ User created:', authData.user.email);

    // Add user to platform_admins table
    const { error: adminError } = await supabase
      .from('platform_admins')
      .insert({
        user_id: authData.user.id,
        is_active: true
      });

    if (adminError) {
      console.error('Error adding to platform_admins:', adminError);
      return;
    }

    console.log('✅ Super admin created successfully!');
    console.log('📧 Email: admin@salon.com');
    console.log('🔑 Password: admin123456');
    console.log('🌐 Access: http://localhost:8081/admin');

  } catch (error) {
    console.error('Unexpected error:', error);
  }
}

createSuperAdmin();