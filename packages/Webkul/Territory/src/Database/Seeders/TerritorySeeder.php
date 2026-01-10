<?php

namespace Webkul\Territory\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TerritorySeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @param  array  $parameters
     * @return void
     */
    public function run($parameters = [])
    {
        DB::table('territory_rules')->delete();

        DB::table('territories')->delete();

        $now = Carbon::now();

        // Create main geographic regions
        $territories = [
            // Global parent territories
            [
                'id'          => 1,
                'name'        => 'North America',
                'code'        => 'NA',
                'description' => 'North American territory including US, Canada, and Mexico',
                'type'        => 'geographic',
                'parent_id'   => null,
                'status'      => 'active',
                'boundaries'  => json_encode([
                    'countries' => ['US', 'CA', 'MX'],
                ]),
                'user_id'     => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'id'          => 2,
                'name'        => 'EMEA',
                'code'        => 'EMEA',
                'description' => 'Europe, Middle East, and Africa region',
                'type'        => 'geographic',
                'parent_id'   => null,
                'status'      => 'active',
                'boundaries'  => json_encode([
                    'countries' => ['GB', 'FR', 'DE', 'IT', 'ES', 'AE', 'SA', 'ZA'],
                ]),
                'user_id'     => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'id'          => 3,
                'name'        => 'APAC',
                'code'        => 'APAC',
                'description' => 'Asia-Pacific region including Australia and New Zealand',
                'type'        => 'geographic',
                'parent_id'   => null,
                'status'      => 'active',
                'boundaries'  => json_encode([
                    'countries' => ['CN', 'JP', 'IN', 'AU', 'SG', 'KR', 'NZ'],
                ]),
                'user_id'     => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            // US sub-regions
            [
                'id'          => 4,
                'name'        => 'US Northeast',
                'code'        => 'US-NE',
                'description' => 'Northeastern United States region',
                'type'        => 'geographic',
                'parent_id'   => 1,
                'status'      => 'active',
                'boundaries'  => json_encode([
                    'country' => 'US',
                    'states'  => ['NY', 'MA', 'PA', 'NJ', 'CT', 'RI', 'VT', 'NH', 'ME'],
                ]),
                'user_id'     => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'id'          => 5,
                'name'        => 'US Southeast',
                'code'        => 'US-SE',
                'description' => 'Southeastern United States region',
                'type'        => 'geographic',
                'parent_id'   => 1,
                'status'      => 'active',
                'boundaries'  => json_encode([
                    'country' => 'US',
                    'states'  => ['FL', 'GA', 'NC', 'SC', 'VA', 'TN', 'AL', 'MS', 'LA'],
                ]),
                'user_id'     => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'id'          => 6,
                'name'        => 'US Midwest',
                'code'        => 'US-MW',
                'description' => 'Midwestern United States region',
                'type'        => 'geographic',
                'parent_id'   => 1,
                'status'      => 'active',
                'boundaries'  => json_encode([
                    'country' => 'US',
                    'states'  => ['IL', 'OH', 'MI', 'IN', 'WI', 'MN', 'IA', 'MO', 'KS', 'NE', 'SD', 'ND'],
                ]),
                'user_id'     => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'id'          => 7,
                'name'        => 'US Southwest',
                'code'        => 'US-SW',
                'description' => 'Southwestern United States region',
                'type'        => 'geographic',
                'parent_id'   => 1,
                'status'      => 'active',
                'boundaries'  => json_encode([
                    'country' => 'US',
                    'states'  => ['TX', 'AZ', 'NM', 'OK', 'AR'],
                ]),
                'user_id'     => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'id'          => 8,
                'name'        => 'US West',
                'code'        => 'US-W',
                'description' => 'Western United States region',
                'type'        => 'geographic',
                'parent_id'   => 1,
                'status'      => 'active',
                'boundaries'  => json_encode([
                    'country' => 'US',
                    'states'  => ['CA', 'WA', 'OR', 'NV', 'ID', 'MT', 'WY', 'CO', 'UT', 'AK', 'HI'],
                ]),
                'user_id'     => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            // EMEA sub-regions
            [
                'id'          => 9,
                'name'        => 'Western Europe',
                'code'        => 'EMEA-WE',
                'description' => 'Western European countries',
                'type'        => 'geographic',
                'parent_id'   => 2,
                'status'      => 'active',
                'boundaries'  => json_encode([
                    'countries' => ['GB', 'FR', 'DE', 'IT', 'ES', 'NL', 'BE', 'CH'],
                ]),
                'user_id'     => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'id'          => 10,
                'name'        => 'Middle East',
                'code'        => 'EMEA-ME',
                'description' => 'Middle Eastern countries',
                'type'        => 'geographic',
                'parent_id'   => 2,
                'status'      => 'active',
                'boundaries'  => json_encode([
                    'countries' => ['AE', 'SA', 'IL', 'QA', 'KW', 'OM'],
                ]),
                'user_id'     => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            // APAC sub-regions
            [
                'id'          => 11,
                'name'        => 'East Asia',
                'code'        => 'APAC-EA',
                'description' => 'East Asian countries',
                'type'        => 'geographic',
                'parent_id'   => 3,
                'status'      => 'active',
                'boundaries'  => json_encode([
                    'countries' => ['CN', 'JP', 'KR', 'TW'],
                ]),
                'user_id'     => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'id'          => 12,
                'name'        => 'South Asia & Pacific',
                'code'        => 'APAC-SAP',
                'description' => 'South Asian and Pacific countries',
                'type'        => 'geographic',
                'parent_id'   => 3,
                'status'      => 'active',
                'boundaries'  => json_encode([
                    'countries' => ['IN', 'AU', 'SG', 'NZ', 'TH', 'MY'],
                ]),
                'user_id'     => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            // Account-based territories
            [
                'id'          => 13,
                'name'        => 'Enterprise Accounts',
                'code'        => 'ENT',
                'description' => 'Large enterprise accounts with 500+ employees',
                'type'        => 'account-based',
                'parent_id'   => null,
                'status'      => 'active',
                'boundaries'  => null,
                'user_id'     => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'id'          => 14,
                'name'        => 'SMB Accounts',
                'code'        => 'SMB',
                'description' => 'Small and medium business accounts',
                'type'        => 'account-based',
                'parent_id'   => null,
                'status'      => 'active',
                'boundaries'  => null,
                'user_id'     => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'id'          => 15,
                'name'        => 'Technology Sector',
                'code'        => 'TECH',
                'description' => 'Technology and software companies',
                'type'        => 'account-based',
                'parent_id'   => null,
                'status'      => 'active',
                'boundaries'  => null,
                'user_id'     => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
        ];

        DB::table('territories')->insert($territories);

        // Create territory rules for auto-assignment
        $rules = [
            // US Northeast rules
            [
                'territory_id' => 4,
                'rule_type'    => 'geographic',
                'field_name'   => 'state',
                'operator'     => 'in',
                'value'        => json_encode(['NY', 'MA', 'PA', 'NJ', 'CT', 'RI', 'VT', 'NH', 'ME']),
                'priority'     => 10,
                'is_active'    => true,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'territory_id' => 4,
                'rule_type'    => 'geographic',
                'field_name'   => 'country',
                'operator'     => '=',
                'value'        => json_encode(['US']),
                'priority'     => 5,
                'is_active'    => true,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            // US Southeast rules
            [
                'territory_id' => 5,
                'rule_type'    => 'geographic',
                'field_name'   => 'state',
                'operator'     => 'in',
                'value'        => json_encode(['FL', 'GA', 'NC', 'SC', 'VA', 'TN', 'AL', 'MS', 'LA']),
                'priority'     => 10,
                'is_active'    => true,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            // US Midwest rules
            [
                'territory_id' => 6,
                'rule_type'    => 'geographic',
                'field_name'   => 'state',
                'operator'     => 'in',
                'value'        => json_encode(['IL', 'OH', 'MI', 'IN', 'WI', 'MN', 'IA', 'MO', 'KS', 'NE', 'SD', 'ND']),
                'priority'     => 10,
                'is_active'    => true,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            // US Southwest rules
            [
                'territory_id' => 7,
                'rule_type'    => 'geographic',
                'field_name'   => 'state',
                'operator'     => 'in',
                'value'        => json_encode(['TX', 'AZ', 'NM', 'OK', 'AR']),
                'priority'     => 10,
                'is_active'    => true,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            // US West rules
            [
                'territory_id' => 8,
                'rule_type'    => 'geographic',
                'field_name'   => 'state',
                'operator'     => 'in',
                'value'        => json_encode(['CA', 'WA', 'OR', 'NV', 'ID', 'MT', 'WY', 'CO', 'UT', 'AK', 'HI']),
                'priority'     => 10,
                'is_active'    => true,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            // North America parent rule
            [
                'territory_id' => 1,
                'rule_type'    => 'geographic',
                'field_name'   => 'country',
                'operator'     => 'in',
                'value'        => json_encode(['US', 'CA', 'MX']),
                'priority'     => 1,
                'is_active'    => true,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            // EMEA rules
            [
                'territory_id' => 9,
                'rule_type'    => 'geographic',
                'field_name'   => 'country',
                'operator'     => 'in',
                'value'        => json_encode(['GB', 'FR', 'DE', 'IT', 'ES', 'NL', 'BE', 'CH']),
                'priority'     => 10,
                'is_active'    => true,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'territory_id' => 10,
                'rule_type'    => 'geographic',
                'field_name'   => 'country',
                'operator'     => 'in',
                'value'        => json_encode(['AE', 'SA', 'IL', 'QA', 'KW', 'OM']),
                'priority'     => 10,
                'is_active'    => true,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'territory_id' => 2,
                'rule_type'    => 'geographic',
                'field_name'   => 'country',
                'operator'     => 'in',
                'value'        => json_encode(['GB', 'FR', 'DE', 'IT', 'ES', 'AE', 'SA', 'ZA']),
                'priority'     => 1,
                'is_active'    => true,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            // APAC rules
            [
                'territory_id' => 11,
                'rule_type'    => 'geographic',
                'field_name'   => 'country',
                'operator'     => 'in',
                'value'        => json_encode(['CN', 'JP', 'KR', 'TW']),
                'priority'     => 10,
                'is_active'    => true,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'territory_id' => 12,
                'rule_type'    => 'geographic',
                'field_name'   => 'country',
                'operator'     => 'in',
                'value'        => json_encode(['IN', 'AU', 'SG', 'NZ', 'TH', 'MY']),
                'priority'     => 10,
                'is_active'    => true,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'territory_id' => 3,
                'rule_type'    => 'geographic',
                'field_name'   => 'country',
                'operator'     => 'in',
                'value'        => json_encode(['CN', 'JP', 'IN', 'AU', 'SG', 'KR', 'NZ']),
                'priority'     => 1,
                'is_active'    => true,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            // Enterprise accounts rules - based on employee count
            [
                'territory_id' => 13,
                'rule_type'    => 'account_size',
                'field_name'   => 'number_of_employees',
                'operator'     => '>=',
                'value'        => json_encode([500]),
                'priority'     => 20,
                'is_active'    => true,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'territory_id' => 13,
                'rule_type'    => 'account_size',
                'field_name'   => 'annual_revenue',
                'operator'     => '>=',
                'value'        => json_encode([10000000]),
                'priority'     => 15,
                'is_active'    => true,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            // SMB accounts rules - based on employee count
            [
                'territory_id' => 14,
                'rule_type'    => 'account_size',
                'field_name'   => 'number_of_employees',
                'operator'     => '<',
                'value'        => json_encode([500]),
                'priority'     => 10,
                'is_active'    => true,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'territory_id' => 14,
                'rule_type'    => 'account_size',
                'field_name'   => 'annual_revenue',
                'operator'     => '<',
                'value'        => json_encode([10000000]),
                'priority'     => 5,
                'is_active'    => true,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            // Technology sector rules - based on industry
            [
                'territory_id' => 15,
                'rule_type'    => 'industry',
                'field_name'   => 'industry',
                'operator'     => 'in',
                'value'        => json_encode(['Technology', 'Software', 'IT Services', 'Computer Hardware']),
                'priority'     => 15,
                'is_active'    => true,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
        ];

        DB::table('territory_rules')->insert($rules);
    }
}
