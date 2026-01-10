# Territory Management User Guide

## Table of Contents
1. [Introduction](#introduction)
2. [Getting Started](#getting-started)
3. [Territory Setup](#territory-setup)
4. [Territory Rules](#territory-rules)
5. [Territory Assignments](#territory-assignments)
6. [Territory Hierarchy](#territory-hierarchy)
7. [Performance Reports and Analytics](#performance-reports-and-analytics)
8. [Maps Visualization](#maps-visualization)
9. [Best Practices](#best-practices)
10. [Troubleshooting](#troubleshooting)

---

## Introduction

Territory Management enables you to organize your sales team by geographic regions, industry verticals, or account characteristics. It provides automated assignment of leads, organizations, and contacts to appropriate territories based on configurable rules, along with comprehensive performance tracking and analytics.

### Key Features
- **Geographic and Account-Based Territories**: Define territories by region or business characteristics
- **Automated Assignment**: Rules-based distribution of leads, organizations, and contacts
- **Territory Hierarchies**: Support for regions, sub-regions, and nested structures
- **Performance Analytics**: Track revenue, conversion rates, and lead counts by territory
- **Map Visualization**: Visual representation of territory coverage and boundaries
- **Assignment Management**: Manual and bulk reassignment capabilities

### Use Cases
- **Regional Sales Teams**: Organize teams by geographic regions (e.g., North America, EMEA, APAC)
- **Industry Verticals**: Assign specialists to specific industries (e.g., Healthcare, Finance, Technology)
- **Account Segmentation**: Route accounts based on size, revenue potential, or other characteristics
- **Hybrid Approaches**: Combine geographic and account-based criteria for complex organizations

---

## Getting Started

### Accessing Territory Management

1. Navigate to **Settings** in the main navigation menu
2. Click on **Territories** in the settings sidebar
3. You'll see the main territories dashboard with a list of all territories

### Required Permissions

To use territory management features, you need the appropriate permissions:
- **View Territories**: `settings.territories.view`
- **Create Territories**: `settings.territories.create`
- **Edit Territories**: `settings.territories.edit`
- **Delete Territories**: `settings.territories.delete`
- **Manage Rules**: `settings.territories.rules.manage`
- **Manage Assignments**: `settings.territories.assignments.manage`
- **View Analytics**: `settings.territories.analytics.view`

Contact your system administrator to request the necessary permissions.

---

## Territory Setup

### Creating a New Territory

1. **Navigate to Territories**
   - Go to Settings > Territories
   - Click the **Create Territory** button

2. **Fill in Basic Information**
   - **Name** (required): A descriptive name for the territory (e.g., "North America - West Coast")
   - **Code** (required): A unique identifier (e.g., "NA-WEST")
   - **Description** (optional): Detailed description of the territory's coverage

3. **Select Territory Type**
   - **Geographic**: Territory based on physical location/region
   - **Account-Based**: Territory based on business characteristics (industry, size, etc.)

4. **Set Territory Status**
   - **Active**: Territory is active and can receive assignments
   - **Inactive**: Territory is disabled and will not receive new assignments

5. **Assign Territory Owner**
   - Select a user who will be responsible for this territory
   - The owner receives notifications and can manage territory assignments

6. **Define Territory Hierarchy** (Optional)
   - Select a parent territory to create a nested structure
   - Leave blank for root-level territories

7. **Set Geographic Boundaries** (Geographic Territories Only)
   - Define boundaries using GeoJSON format
   - Draw on the map interface (if available)
   - Copy/paste GeoJSON coordinates

8. **Save the Territory**
   - Click **Save** to create the territory
   - The territory will appear in the territories list

### Editing a Territory

1. Go to Settings > Territories
2. Click the **Edit** icon next to the territory you want to modify
3. Update the desired fields
4. Click **Update** to save changes

**Note**: You cannot set a territory as its own parent or as a child of its own descendants (circular reference prevention).

### Deleting a Territory

1. Go to Settings > Territories
2. Click the **Delete** icon next to the territory
3. Confirm the deletion in the popup dialog

**Important**:
- Deleting a territory will also delete all associated rules and assignments
- Child territories will become root-level territories (parent_id set to null)
- This action cannot be undone

### Activating/Deactivating Territories

To temporarily disable a territory without deleting it:

1. Edit the territory
2. Change the **Status** to **Inactive**
3. Save the changes

Inactive territories:
- Will not receive new automatic assignments
- Existing assignments remain intact
- Rules are not evaluated for inactive territories
- Can be reactivated at any time

---

## Territory Rules

Territory rules define the criteria for automatically assigning leads, organizations, and contacts to territories. When an entity is created, the system evaluates all active rules and assigns it to the best matching territory.

### Understanding Rule Components

Each rule consists of:
- **Field Name**: The entity field to evaluate (e.g., `country`, `industry`, `annual_revenue`)
- **Operator**: How to compare the field value (equals, contains, greater than, etc.)
- **Value**: The value to compare against
- **Priority**: Rule evaluation order (higher priority = evaluated first)
- **Status**: Active or inactive

### Creating Territory Rules

1. **Navigate to Territory Rules**
   - Go to Settings > Territories
   - Click **View** on a territory
   - Navigate to the **Rules** tab
   - Click **Create Rule**

2. **Define Rule Criteria**
   - **Field Name**: Select or enter the field to evaluate
     - For Leads: `country`, `state`, `city`, `industry`, `source`, etc.
     - For Organizations: `country`, `industry`, `annual_revenue`, `employee_count`, etc.
     - For Persons: `country`, `job_title`, `department`, etc.

   - **Operator**: Select the comparison method
     - `=` (equals): Exact match
     - `!=` (not equals): Value must not match
     - `>` (greater than): Numeric comparison
     - `<` (less than): Numeric comparison
     - `>=` (greater than or equal): Numeric comparison
     - `<=` (less than or equal): Numeric comparison
     - `in`: Value must be in the provided list
     - `not_in`: Value must not be in the provided list
     - `contains`: Field value contains the string
     - `starts_with`: Field value starts with the string
     - `ends_with`: Field value ends with the string
     - `is_null`: Field value is null/empty
     - `is_not_null`: Field value exists
     - `between`: Numeric value is between two values

   - **Value**: Enter the value to compare
     - For `in` and `not_in` operators: Comma-separated list (e.g., "USA,Canada,Mexico")
     - For `between` operator: Two values separated by comma (e.g., "100000,500000")
     - For other operators: Single value

3. **Set Rule Type** (Optional)
   - **Geographic**: Rule based on location fields
   - **Industry**: Rule based on industry classification
   - **Account Size**: Rule based on company size/revenue
   - **Custom**: Any other custom criteria

4. **Set Priority**
   - Higher numbers = higher priority
   - Rules with higher priority are evaluated first
   - Default: 0

5. **Set Rule Status**
   - **Active**: Rule is evaluated during assignment
   - **Inactive**: Rule is ignored

6. **Save the Rule**
   - Click **Save** to add the rule to the territory

### Rule Evaluation Logic

When an entity (lead, organization, or person) is created:

1. The system retrieves all active territories with active rules
2. Territories are evaluated based on rule priority (highest first)
3. **All rules** in a territory must match for the territory to be selected (AND logic)
4. The first territory where all rules match is assigned to the entity
5. If no territories match, the entity remains unassigned

### Example Rule Configurations

#### Example 1: Geographic Territory (USA - West Coast)
```
Rule 1: Field: country, Operator: =, Value: USA, Priority: 100
Rule 2: Field: state, Operator: in, Value: California,Oregon,Washington, Priority: 100
```

#### Example 2: Industry-Based Territory (Healthcare)
```
Rule 1: Field: industry, Operator: =, Value: Healthcare, Priority: 90
Rule 2: Field: country, Operator: =, Value: USA, Priority: 90
```

#### Example 3: Enterprise Accounts
```
Rule 1: Field: annual_revenue, Operator: >=, Value: 1000000, Priority: 95
Rule 2: Field: employee_count, Operator: >=, Value: 500, Priority: 95
```

#### Example 4: Combining Geographic and Account Criteria
```
Rule 1: Field: country, Operator: =, Value: USA, Priority: 85
Rule 2: Field: industry, Operator: in, Value: Technology,Software,IT, Priority: 85
Rule 3: Field: annual_revenue, Operator: between, Value: 100000,1000000, Priority: 85
```

### Managing Rules

#### Editing Rules
1. Navigate to the territory's rules page
2. Click the **Edit** icon next to the rule
3. Modify the desired fields
4. Click **Update**

#### Deleting Rules
1. Navigate to the territory's rules page
2. Click the **Delete** icon next to the rule
3. Confirm the deletion

#### Toggling Rule Status
1. Navigate to the territory's rules page
2. Click the **Status Toggle** icon to quickly activate/deactivate a rule

#### Updating Rule Priority
1. Navigate to the territory's rules page
2. Use the **Priority** controls to adjust rule order
3. Click **Update Priorities** to save changes

#### Bulk Priority Updates
1. Navigate to the territory's rules page
2. Click **Bulk Update Priorities**
3. Drag and drop rules to reorder them
4. Click **Save** to apply the new priority order

### Rule Best Practices

1. **Keep Rules Simple**: Use clear, specific criteria that are easy to understand
2. **Test Your Rules**: Create test leads/organizations to verify rule behavior
3. **Use Priority Wisely**: Assign higher priority to more specific territories
4. **Avoid Overlapping Rules**: Ensure each entity can be uniquely classified
5. **Document Rule Logic**: Use the description field to explain complex rules
6. **Regular Review**: Periodically review rules to ensure they match current business needs
7. **Start Broad, Then Narrow**: Create general territories first, then add specific ones

---

## Territory Assignments

Territory assignments link leads, organizations, and persons to specific territories. Assignments can be created automatically through rules or manually by users.

### Automatic Assignments

Automatic assignment occurs when:
- A new lead is created
- A new organization is created
- A new person (contact) is created

The system evaluates all active territory rules and assigns the entity to the first matching territory.

**Assignment Process:**
1. Entity is created via form, import, or API
2. System retrieves all active territories (ordered by priority)
3. For each territory, all active rules are evaluated
4. First territory where **all rules match** is selected
5. Assignment record is created with type: `automatic`
6. Assignment history is recorded
7. Optionally, entity ownership is transferred to territory owner

### Manual Assignments

You can manually assign entities to territories when automatic assignment doesn't apply.

#### Creating Manual Assignments

1. **Navigate to Assignments**
   - Go to Settings > Territories
   - Click **Assignments** in the sidebar

2. **Create New Assignment**
   - Click **Create Assignment**
   - Select the territory
   - Select the entity type (Lead, Organization, Person)
   - Enter the entity ID
   - Toggle **Transfer Ownership** (optional)
   - Click **Save**

3. **Assignment Created**
   - Assignment record is created with type: `manual`
   - Entity is now linked to the territory
   - Assignment history is recorded

### Viewing Assignments

#### All Assignments
1. Go to Settings > Territories > Assignments
2. View the list of all assignments across all territories
3. Use filters to find specific assignments:
   - Filter by territory
   - Filter by entity type
   - Filter by assignment type (manual/automatic)
   - Search by entity name

#### Territory-Specific Assignments
1. Go to Settings > Territories
2. Click **View** on a territory
3. Navigate to the **Assignments** tab
4. View all assignments for that specific territory

### Reassigning Entities

When business needs change, you can reassign entities to different territories.

#### Single Reassignment

1. **Navigate to Assignments**
   - Go to Settings > Territories > Assignments

2. **Initiate Reassignment**
   - Click the **Reassign** icon next to the assignment

3. **Select New Territory**
   - Choose the new territory from the dropdown
   - Current territory is disabled in the list
   - Toggle **Transfer Ownership** to assign entity to new territory owner

4. **Confirm Reassignment**
   - Click **Reassign**
   - New assignment is created
   - Old assignment is replaced
   - History is recorded with both old and new territories

#### Bulk Reassignment

For reassigning multiple entities at once:

1. **Select Entities**
   - Go to Settings > Territories > Assignments
   - Check the boxes next to the assignments you want to reassign
   - Or use **Select All** for bulk operations

2. **Initiate Bulk Reassignment**
   - Click **Bulk Reassign** button at the top
   - Reassignment modal appears

3. **Configure Reassignment**
   - Select the new territory for all selected entities
   - Toggle **Transfer Ownership** (applies to all)

4. **Execute Reassignment**
   - Click **Reassign Selected**
   - All selected entities are reassigned
   - Success message shows count of reassigned entities

### Deleting Assignments

To remove territory assignments:

1. Go to Settings > Territories > Assignments
2. Click the **Delete** icon next to the assignment
3. Confirm the deletion
4. Assignment is removed (entity becomes unassigned)

**Note**: Deleting an assignment does not delete the entity (lead, organization, or person), only the territory linkage.

### Assignment History

View the complete history of territory assignments for an entity:

1. Go to Settings > Territories > Assignments
2. Click **View History** next to an assignment
3. Timeline view shows:
   - All past territories
   - Assignment dates and times
   - Who created each assignment
   - Assignment type (manual/automatic)
   - Ownership transfer status

### Transfer Ownership

The **Transfer Ownership** feature automatically changes the entity's owner to the territory owner when an assignment is created or changed.

**When to Use:**
- ✅ When territory owner should be responsible for all assigned entities
- ✅ For automatic routing of inbound leads to regional managers
- ✅ When reorganizing team structure

**When Not to Use:**
- ❌ When current entity owner should maintain responsibility
- ❌ For reporting purposes only (no ownership change needed)
- ❌ When territory owner shouldn't have access to all entities

**Behavior:**
- Enabled by default for new assignments
- Can be toggled on/off for each assignment
- Updates entity's `user_id` field to match territory's `user_id`
- Original owner information is preserved in assignment history

---

## Territory Hierarchy

Territory hierarchies allow you to organize territories in parent-child relationships, enabling nested structures like Regions → Countries → States → Cities.

### Creating Territory Hierarchies

1. **Create Root Territory**
   - Create a territory without a parent (e.g., "North America")
   - This becomes a top-level territory

2. **Create Child Territories**
   - Create a new territory
   - Select the parent territory from the dropdown
   - Child territory inherits context from parent
   - Example: Parent: "North America", Child: "USA - West Coast"

3. **Create Multi-Level Hierarchies**
   - Create grandchild territories by selecting a child as parent
   - Example hierarchy:
     ```
     Global
     ├── North America
     │   ├── USA - West Coast
     │   │   ├── California
     │   │   └── Oregon
     │   └── USA - East Coast
     │       ├── New York
     │       └── Massachusetts
     └── EMEA
         ├── UK & Ireland
         └── Western Europe
     ```

### Viewing Territory Hierarchy

#### Hierarchy Tree View

1. Go to Settings > Territories
2. Click **View Hierarchy** button
3. Interactive tree view shows:
   - All territories organized by parent-child relationships
   - Expand/collapse controls for each level
   - Visual indentation for hierarchy levels
   - Territory status indicators
   - Quick links to view each territory

#### Navigating the Hierarchy

- **Expand/Collapse**: Click the arrow icon to show/hide children
- **Expand All**: Button to expand the entire tree
- **Collapse All**: Button to collapse the entire tree
- **Click Territory**: Navigate to territory detail page

### Hierarchy Rules and Constraints

1. **Circular References Prevented**
   - Cannot set a territory as its own parent
   - Cannot set a descendant as parent (e.g., grandchild cannot be parent of grandparent)
   - System validates and prevents these scenarios

2. **Orphaned Territories**
   - Deleting a parent territory sets children's `parent_id` to null
   - Children become root-level territories
   - No territories are deleted when parent is deleted

3. **Hierarchy Depth**
   - No technical limit on hierarchy depth
   - Best practice: Keep hierarchies 3-5 levels deep for manageability

### Using Hierarchies Effectively

#### Example 1: Geographic Hierarchy
```
Global Sales
├── Americas
│   ├── North America
│   │   ├── USA
│   │   └── Canada
│   └── Latin America
│       ├── Mexico
│       └── Brazil
├── EMEA
│   ├── Europe
│   ├── Middle East
│   └── Africa
└── APAC
    ├── Asia
    ├── Australia
    └── Pacific Islands
```

#### Example 2: Industry + Geography Hybrid
```
Enterprise Sales
├── Healthcare - USA
│   ├── Healthcare - West
│   └── Healthcare - East
├── Technology - USA
│   ├── Technology - West
│   └── Technology - East
└── Financial Services - USA
    ├── Banking - West
    └── Insurance - East
```

#### Example 3: Account Size Hierarchy
```
Sales Territories
├── Enterprise (>$10M ARR)
│   ├── Enterprise - North
│   └── Enterprise - South
├── Mid-Market ($1M-$10M ARR)
│   ├── Mid-Market - North
│   └── Mid-Market - South
└── SMB (<$1M ARR)
    ├── SMB - North
    └── SMB - South
```

### Hierarchy Best Practices

1. **Plan Before Building**: Design your hierarchy structure before creating territories
2. **Consistent Naming**: Use clear naming conventions (e.g., "Region - Country - State")
3. **Balanced Trees**: Avoid very deep or very wide hierarchies
4. **Logical Groupings**: Group territories by logical business units
5. **Document Structure**: Maintain documentation of your hierarchy design
6. **Review Regularly**: Reassess hierarchy as business needs change

---

## Performance Reports and Analytics

Territory analytics provide insights into territory performance, helping you optimize coverage and identify growth opportunities.

### Accessing Analytics

1. Go to Settings > Territories
2. Click **Analytics** in the sidebar
3. The analytics dashboard displays with multiple sections

### Analytics Dashboard Overview

The dashboard is divided into several sections:

#### 1. Overview Metrics (Top Cards)

Four key performance indicators:

- **Total Leads**: Total number of leads across all territories
- **Won Leads**: Number of leads converted to customers
- **Average Conversion Rate**: Percentage of leads converted (won/total)
- **Total Revenue**: Sum of revenue from all won opportunities

#### 2. Territory Performance Chart

Interactive bar chart showing:
- **Revenue** (left axis): Total revenue per territory
- **Conversion Rate** (right axis): Win rate percentage per territory
- Dual-axis visualization for comparing different metrics
- Hover over bars to see exact values
- Click territory name to navigate to territory details

#### 3. Territory Performance Table

Comprehensive table with all territories showing:
- Territory name
- Total leads count
- Won leads count
- Conversion rate (percentage)
- Total revenue (formatted currency)
- Sortable columns for easy comparison
- Click row to view territory details

#### 4. Top Performers Sections

Three separate leaderboards:

- **Top Territories by Revenue**
  - Top 5 territories ranked by total revenue
  - Shows revenue amount for each
  - Numbered ranking badges

- **Top Territories by Conversion Rate**
  - Top 5 territories ranked by win percentage
  - Shows conversion rate for each
  - Identifies most efficient territories

- **Top Territories by Lead Count**
  - Top 5 territories ranked by total leads
  - Shows lead count for each
  - Identifies territories with highest volume

### Advanced Analytics Features

#### Filter by Date Range

1. Use the date range picker at the top of the analytics page
2. Select start date and end date
3. Click **Apply** to filter all metrics
4. All charts and tables update to show data for selected period

#### Territory-Specific Analytics

1. Navigate to a specific territory
2. Click the **Analytics** tab
3. View performance metrics for just that territory:
   - Lead count and trend
   - Conversion rate over time
   - Revenue progression
   - Top performing entities within territory

#### Performance Trends

View territory performance over time:

1. Navigate to Territory Analytics
2. Select a territory
3. View the **Trend** section showing:
   - Monthly performance over last 12 months
   - Revenue trend line
   - Conversion rate trend
   - Lead volume trend
   - Identifies seasonality and patterns

#### Territory Comparison

Compare multiple territories side-by-side:

1. Click **Compare Territories**
2. Select 2-10 territories to compare
3. View comparison across:
   - Total leads
   - Won leads
   - Conversion rates
   - Revenue
   - Average deal size
4. Identify best and worst performers

#### Analytics by Territory Type

View aggregated performance by territory type:

1. Navigate to Territory Analytics
2. Click **By Type** tab
3. Compare:
   - Geographic territories performance
   - Account-based territories performance
   - Overall differences between strategies

### Export Analytics

Export analytics data for external analysis:

1. Navigate to desired analytics view
2. Click **Export** button
3. Choose format: CSV, Excel, or PDF
4. Data downloads with all visible columns and filters applied

### Interpreting Analytics

#### Key Metrics Explained

**Conversion Rate**
- Formula: (Won Leads / Total Leads) × 100
- Good: >20%
- Average: 10-20%
- Needs Improvement: <10%

**Average Deal Size**
- Formula: Total Revenue / Won Leads
- Indicates quality of leads in territory
- Higher value suggests better lead quality or larger accounts

**Lead Velocity**
- Rate of new leads assigned to territory
- Helps identify capacity constraints
- Useful for resource planning

#### Identifying Issues

**Low Conversion Rate Possible Causes:**
- Poor lead quality
- Insufficient resources
- Wrong territory rules (mismatched leads)
- Training needs
- Competitive challenges in region

**High Lead Count, Low Revenue Possible Causes:**
- Small deal sizes (adjust territory rules for account size)
- Long sales cycles
- Qualification issues
- Pricing challenges

**Uneven Distribution Possible Causes:**
- Territory rules too broad/narrow
- Geographic imbalances
- Market saturation in some areas
- Rule priority conflicts

### Analytics Best Practices

1. **Regular Review**: Check analytics weekly or monthly
2. **Set Benchmarks**: Establish performance targets for each territory
3. **Compare Fairly**: Consider market size and maturity when comparing territories
4. **Action-Oriented**: Use insights to drive changes (rule adjustments, resource allocation)
5. **Trend Focus**: Look at trends over time, not just snapshots
6. **Combine Data**: Use CRM data alongside territory analytics for complete picture

---

## Maps Visualization

The map visualization feature provides a geographic view of territory coverage, boundaries, and entity distribution.

### Accessing Territory Maps

1. Go to Settings > Territories
2. Click **Map View** in the sidebar
3. Interactive map displays with all territories

### Map Features

#### Territory Boundaries

For geographic territories with defined boundaries:
- Boundaries shown as colored polygons on map
- Each territory has a unique color
- Hover over boundary to see territory name and details
- Click boundary to navigate to territory page

#### Entity Markers

View leads, organizations, and persons on the map:
- **Lead Markers**: Blue pins showing lead locations
- **Organization Markers**: Green pins showing company locations
- **Person Markers**: Purple pins showing contact locations
- Hover over marker to see entity name and details
- Click marker to view entity details

#### Map Controls

- **Zoom In/Out**: Use +/- buttons or scroll wheel
- **Pan**: Click and drag to move map
- **Reset View**: Button to return to default view
- **Full Screen**: Expand map to full screen
- **Layer Toggle**: Show/hide different layers:
  - Territory boundaries
  - Leads
  - Organizations
  - Persons
  - Heat map

### Using the Map

#### Viewing Territory Coverage

1. Open the map view
2. Enable **Territory Boundaries** layer
3. Visually assess:
   - Coverage gaps (areas without territories)
   - Overlapping territories
   - Territory sizes and distribution
   - Geographic balance

#### Identifying Entity Distribution

1. Enable entity marker layers
2. Observe clustering patterns
3. Identify:
   - High-density areas (many leads/accounts)
   - Under-served regions
   - Opportunities for territory expansion
   - Misaligned territory boundaries

#### Heat Map View

The heat map shows entity density:
1. Enable **Heat Map** layer
2. Red areas = high entity density
3. Blue areas = low entity density
4. Use to:
   - Identify hotspots
   - Plan territory boundaries
   - Balance workload
   - Spot market opportunities

### Drawing Territory Boundaries

For new geographic territories:

1. **Create Territory**
   - Start creating a new territory
   - Select type: **Geographic**

2. **Open Map Tool**
   - In the boundaries section, click **Draw on Map**
   - Map interface opens

3. **Draw Boundary**
   - Select drawing tool (polygon, rectangle, circle)
   - Click map to create points
   - Double-click to close polygon
   - Adjust points as needed

4. **Save Boundary**
   - Click **Save**
   - GeoJSON data automatically populated
   - Boundary visible on territory map

### Editing Boundaries

1. Edit an existing geographic territory
2. Click **Edit Boundary** in the map section
3. Adjust polygon points by dragging
4. Add new points by clicking on edges
5. Delete points by clicking and pressing delete
6. Save changes

### Map Best Practices

1. **Define Clear Boundaries**: Avoid overlapping territories
2. **Regular Updates**: Update boundaries as market coverage changes
3. **Balance Coverage**: Ensure territories are roughly equal in opportunity
4. **Visual Review**: Regularly check map view for coverage gaps
5. **Coordinate with Rules**: Ensure boundary definitions match territory rules
6. **Document Boundaries**: Keep notes on boundary decisions and rationale

### GeoJSON Format

For manual boundary definition, use GeoJSON format:

```json
{
  "type": "Polygon",
  "coordinates": [
    [
      [-122.4194, 37.7749],
      [-122.4194, 38.0000],
      [-121.8000, 38.0000],
      [-121.8000, 37.7749],
      [-122.4194, 37.7749]
    ]
  ]
}
```

**Key points:**
- `type`: "Polygon" for area boundaries
- `coordinates`: Array of coordinate arrays [longitude, latitude]
- First and last coordinates must be identical (closed polygon)
- Longitude first, then latitude (opposite of common lat/long notation)

---

## Best Practices

### Territory Design

1. **Start Simple**
   - Begin with a few broad territories
   - Add complexity as you learn what works
   - Avoid over-segmentation initially

2. **Use Clear Naming Conventions**
   - Descriptive names (e.g., "USA - West Coast - Enterprise")
   - Consistent format across all territories
   - Include key attributes in name

3. **Balance Territory Size**
   - Roughly equal opportunity potential
   - Similar workload expectations
   - Consider travel time for geographic territories

4. **Define Clear Ownership**
   - Each territory should have one primary owner
   - Document owner responsibilities
   - Plan for owner transitions

5. **Document Everything**
   - Use description fields extensively
   - Maintain separate documentation of territory strategy
   - Document rule logic and rationale

### Rule Configuration

1. **Test Before Deploying**
   - Create test leads/accounts to verify rules
   - Check for unintended matches
   - Validate priority order

2. **Keep Rules Simple**
   - Avoid overly complex criteria
   - Use as few rules as necessary
   - Make rules easy to explain

3. **Regular Rule Audits**
   - Review rules quarterly
   - Update for business changes
   - Remove obsolete rules

4. **Avoid Rule Conflicts**
   - Ensure rules are mutually exclusive where possible
   - Use priority to handle overlaps
   - Document intentional overlaps

5. **Plan for Growth**
   - Create rules that scale
   - Build in flexibility for market changes
   - Avoid hard-coded values when possible

### Assignment Management

1. **Favor Automatic Assignment**
   - Use rules for predictable patterns
   - Reserve manual assignment for exceptions
   - Document reasons for manual assignments

2. **Regular Cleanup**
   - Review unassigned entities
   - Fix assignment errors promptly
   - Update rules to prevent future issues

3. **Ownership Transfers**
   - Communicate transfers to affected parties
   - Have clear policies on when to transfer ownership
   - Track transfer history

4. **Bulk Operations**
   - Use bulk reassignment for reorganizations
   - Test on small sample first
   - Communicate to team before major changes

### Performance Monitoring

1. **Set Clear KPIs**
   - Define success metrics for each territory
   - Align with overall business objectives
   - Review KPIs regularly

2. **Regular Reviews**
   - Weekly operational reviews
   - Monthly performance analysis
   - Quarterly strategic planning

3. **Benchmark and Compare**
   - Compare territories fairly (similar markets)
   - Track trends over time
   - Identify best practices from top performers

4. **Act on Insights**
   - Use analytics to drive decisions
   - Adjust rules based on performance
   - Reallocate resources to opportunities

### Organizational Change Management

1. **Communicate Changes**
   - Notify team members of territory changes
   - Explain rationale for changes
   - Provide adequate transition time

2. **Train Users**
   - Ensure team understands territory system
   - Provide documentation and guides
   - Offer hands-on training sessions

3. **Gradual Rollout**
   - Start with pilot territories
   - Gather feedback and adjust
   - Roll out incrementally

4. **Maintain Flexibility**
   - Be ready to adjust based on results
   - Don't be afraid to pivot
   - Keep channels open for feedback

---

## Troubleshooting

### Common Issues and Solutions

#### Issue: Leads Not Being Assigned Automatically

**Possible Causes:**
- Territory is inactive
- Territory rules are inactive
- No rules defined for territory
- Entity doesn't match any rule criteria
- Rule priority conflicts

**Solutions:**
1. Check territory status (Settings > Territories)
2. Verify rules are active (Territory > Rules tab)
3. Review rule criteria to ensure they match incoming leads
4. Test rules with sample data
5. Check rule priority and evaluation order
6. Enable debug logging to see rule evaluation details

#### Issue: Wrong Territory Assigned

**Possible Causes:**
- Rule criteria too broad
- Priority order incorrect
- Overlapping rules between territories
- Field values not what you expected

**Solutions:**
1. Review the rule that caused the assignment
2. Add more specific criteria to narrow matching
3. Adjust priority to favor more specific territories
4. Check entity field values to verify data quality
5. Create more specific rules for edge cases
6. Consider using nested territories for complex scenarios

#### Issue: Entity Assigned to Multiple Territories

**Possible Causes:**
- This shouldn't happen - system assigns to first match only
- May have multiple assignment records (manual + automatic)

**Solutions:**
1. Check assignment history
2. Delete duplicate assignments
3. Keep most appropriate assignment
4. Review process that created duplicates

#### Issue: Territory Hierarchy Not Displaying Correctly

**Possible Causes:**
- Circular reference in parent-child relationships
- Orphaned territories (parent deleted)
- Data corruption

**Solutions:**
1. Check each territory's parent_id field
2. Look for territories with invalid parent_id
3. Rebuild hierarchy by reassigning parents
4. Use SQL to identify circular references
5. Contact administrator if data corruption suspected

#### Issue: Analytics Showing Incorrect Data

**Possible Causes:**
- Date range filter applied
- Recent assignments not yet reflected
- Cache not refreshed
- Entity data missing or incorrect

**Solutions:**
1. Clear date range filter
2. Refresh browser cache (Ctrl+F5)
3. Verify entity data (revenue, won status, etc.)
4. Check that assignments exist for entities in question
5. Refresh analytics dashboard
6. Check for recent system updates

#### Issue: Map Not Loading or Showing Boundaries

**Possible Causes:**
- Invalid GeoJSON format
- Map library not loaded
- Browser compatibility issue
- Network connectivity issue

**Solutions:**
1. Validate GeoJSON using online validator
2. Check browser console for JavaScript errors
3. Try different browser
4. Check internet connection
5. Clear browser cache
6. Verify boundaries field contains valid data

#### Issue: Cannot Delete Territory

**Possible Causes:**
- Territory has active assignments
- Territory has child territories
- Permission issue

**Solutions:**
1. Reassign entities to different territory first
2. Reassign or delete child territories first
3. Check user permissions
4. Try deactivating instead of deleting
5. Contact administrator if issue persists

#### Issue: Bulk Reassignment Failing

**Possible Causes:**
- Too many entities selected
- Target territory inactive
- Permission issue
- Timeout or server issue

**Solutions:**
1. Reduce number of entities (batch in smaller groups)
2. Verify target territory is active
3. Check user permissions for bulk operations
4. Try reassigning smaller batches
5. Contact administrator if server issues

### Getting Help

If you encounter issues not covered here:

1. **Check System Logs**
   - View application logs for error messages
   - Look for territory-related errors
   - Note error timestamps and details

2. **Contact Support**
   - Provide detailed description of issue
   - Include steps to reproduce
   - Attach screenshots if applicable
   - Share relevant territory/rule IDs

3. **Documentation Resources**
   - API documentation for advanced integrations
   - Database schema documentation
   - System administration guide

4. **Community Resources**
   - User forums and discussion boards
   - Knowledge base articles
   - Video tutorials and webinars

---

## Appendix

### Glossary

- **Territory**: A defined region or business segment for organizing sales activities
- **Territory Rule**: Criteria used to automatically assign entities to territories
- **Territory Assignment**: Link between an entity (lead, organization, person) and a territory
- **Territory Owner**: User responsible for managing a territory
- **Geographic Territory**: Territory defined by physical location/region
- **Account-Based Territory**: Territory defined by business characteristics
- **Territory Hierarchy**: Parent-child relationships between territories
- **Auto-Assignment**: Automatic entity assignment based on territory rules
- **Manual Assignment**: User-created assignment bypassing rule evaluation
- **GeoJSON**: Standard format for encoding geographic data structures
- **Conversion Rate**: Percentage of leads converted to customers
- **Territory Analytics**: Performance metrics and reports for territories

### Field Reference

#### Territory Fields
- `name`: Territory display name
- `code`: Unique territory identifier
- `description`: Detailed territory description
- `type`: Territory type (geographic/account-based)
- `status`: Territory status (active/inactive)
- `parent_id`: Parent territory ID for hierarchies
- `user_id`: Territory owner user ID
- `boundaries`: GeoJSON boundary data (geographic only)

#### Territory Rule Fields
- `field_name`: Entity field to evaluate
- `operator`: Comparison operator
- `value`: Value to compare against
- `type`: Rule type (geographic/industry/account_size/custom)
- `priority`: Rule evaluation order
- `status`: Rule status (active/inactive)

#### Territory Assignment Fields
- `territory_id`: Assigned territory ID
- `assignable_type`: Entity type (Lead/Organization/Person)
- `assignable_id`: Entity ID
- `assigned_by`: User who created assignment
- `assignment_type`: Assignment type (automatic/manual)
- `assigned_at`: Assignment timestamp

### Supported Operators

| Operator | Description | Example |
|----------|-------------|---------|
| `=` | Equals (exact match) | country = "USA" |
| `!=` | Not equals | industry != "Retail" |
| `>` | Greater than | annual_revenue > 1000000 |
| `<` | Less than | employee_count < 50 |
| `>=` | Greater than or equal | deal_value >= 10000 |
| `<=` | Less than or equal | age <= 65 |
| `in` | In list | state in "CA,NY,TX" |
| `not_in` | Not in list | country not_in "CA,MX" |
| `contains` | Contains substring | email contains "@gmail.com" |
| `starts_with` | Starts with | phone starts_with "+1" |
| `ends_with` | Ends with | website ends_with ".edu" |
| `is_null` | Is null/empty | middle_name is_null |
| `is_not_null` | Is not null/has value | phone is_not_null |
| `between` | Between two values | revenue between "100000,500000" |

### API Endpoints Reference

For developers integrating with territory management:

**Territories:**
- `GET /api/territories` - List all territories
- `GET /api/territories/{id}` - Get territory details
- `POST /api/territories` - Create territory
- `PUT /api/territories/{id}` - Update territory
- `DELETE /api/territories/{id}` - Delete territory
- `GET /api/territories/{id}/hierarchy` - Get territory hierarchy

**Rules:**
- `GET /api/territories/{id}/rules` - List territory rules
- `POST /api/territories/{id}/rules` - Create rule
- `PUT /api/territories/{id}/rules/{ruleId}` - Update rule
- `DELETE /api/territories/{id}/rules/{ruleId}` - Delete rule

**Assignments:**
- `GET /api/territories/assignments` - List all assignments
- `POST /api/territories/assignments` - Create assignment
- `DELETE /api/territories/assignments/{id}` - Delete assignment
- `POST /api/territories/assignments/reassign` - Reassign entity
- `GET /api/territories/assignments/history` - Get assignment history

**Analytics:**
- `GET /api/territories/analytics/overview` - Overall metrics
- `GET /api/territories/analytics/{id}` - Territory-specific metrics
- `GET /api/territories/analytics/compare` - Compare territories

---

## Revision History

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | 2026-01-11 | Initial release of Territory Management user guide |

---

**Need Help?** Contact your system administrator or support team for assistance with territory management.
