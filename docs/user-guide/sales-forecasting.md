# Sales Forecasting User Guide

## Table of Contents
1. [Introduction](#introduction)
2. [Getting Started](#getting-started)
3. [Understanding Deal Scores](#understanding-deal-scores)
4. [Using Forecasts](#using-forecasts)
5. [Scenario Modeling](#scenario-modeling)
6. [Forecast Accuracy Tracking](#forecast-accuracy-tracking)
7. [Team Forecasting](#team-forecasting)
8. [Analytics and Insights](#analytics-and-insights)
9. [Best Practices](#best-practices)
10. [Troubleshooting](#troubleshooting)

---

## Introduction

Sales Forecasting is an AI-powered feature that helps you predict revenue, prioritize deals, and improve forecast accuracy. It leverages historical data, pipeline analytics, and machine learning algorithms to provide actionable insights for sales planning and deal management.

### Key Features
- **AI-Powered Deal Scoring**: Automatic scoring of deals based on engagement, velocity, value, and historical patterns
- **Win Probability Prediction**: Data-driven predictions of deal closure likelihood
- **Revenue Forecasting**: Forecast revenue by week, month, or quarter with multiple scenarios
- **Accuracy Tracking**: Compare forecasts vs. actual results to improve predictions
- **Team Analytics**: Aggregate forecasts across teams for better resource planning
- **Scenario Modeling**: Best case, worst case, and weighted forecast scenarios
- **Trend Analysis**: Historical performance tracking and trend identification

### Use Cases
- **Sales Reps**: Prioritize high-scoring deals to maximize win rates and quota attainment
- **Sales Managers**: Generate accurate forecasts for team planning and resource allocation
- **Revenue Operations**: Track forecast accuracy and improve prediction models over time
- **Executive Leadership**: Make data-driven decisions based on revenue projections and trends

---

## Getting Started

### Accessing Sales Forecasting

Sales forecasting features are integrated throughout the CRM:

1. **Forecast Dashboard**: Navigate to **Leads** > **Forecasts** in the main menu
2. **Deal Scores**: View deal scores on individual lead detail pages
3. **Analytics**: Access forecasting analytics through **Leads** > **Forecasts** > **Analytics**

### Required Permissions

To use forecasting features, you need appropriate permissions:
- **View Forecasts**: View your own forecasts and deal scores
- **Generate Forecasts**: Create new forecasts for your pipeline
- **View Team Forecasts**: Access team-level forecasts (managers)
- **Manage Forecasting Settings**: Configure forecast settings (admins)

Contact your system administrator to request necessary permissions.

### Initial Setup

Before generating forecasts, ensure:

1. **Pipeline Data**: You have active deals in your pipeline
2. **Historical Data**: The system has analyzed historical conversion patterns (automatic)
3. **Deal Information**: Leads have complete information (value, stage, expected close date)
4. **Stage Probabilities**: Pipeline stages have probability values configured

**Note**: The system automatically processes historical data daily to improve forecast accuracy.

---

## Understanding Deal Scores

Deal scores help you prioritize opportunities by predicting which deals are most likely to close. Each deal receives a composite score from 0-100 based on multiple factors.

### What is a Deal Score?

A deal score is an AI-generated rating that indicates the likelihood of successfully closing a deal. Higher scores (80-100) indicate strong opportunities, while lower scores (0-50) suggest deals that need attention or may not close.

### Score Components

Each deal score is calculated from five weighted factors:

#### 1. Engagement Score (30% weight)

Measures the level of interaction with the prospect:
- **Email count**: Number of emails exchanged
- **Activity count**: Meetings, calls, demos completed
- **Recency of contact**: How recently you've engaged with the prospect
- **Response rate**: How responsive the prospect is

**High engagement** (80-100): Regular communication, quick responses, high activity
**Medium engagement** (50-79): Moderate communication, occasional delays
**Low engagement** (0-49): Minimal contact, slow responses, inactive prospect

#### 2. Velocity Score (25% weight)

Evaluates how quickly the deal is progressing:
- **Time in current stage**: Days since last stage change
- **Total pipeline time**: Overall time in pipeline vs. average
- **Expected close date**: Proximity to expected close date
- **Stage progression rate**: Speed of movement through pipeline

**High velocity** (80-100): Moving quickly through stages, ahead of schedule
**Medium velocity** (50-79): Normal progression, on track
**Low velocity** (0-49): Stalled, delayed, or slow movement

#### 3. Value Score (20% weight)

Assesses the deal size relative to your typical deals:
- **Deal value**: Opportunity value in currency
- **Average deal size**: Comparison to your historical average
- **Value percentile**: Where this deal ranks in your pipeline

**High value** (80-100): Large deal, well above average
**Medium value** (50-79): Average to slightly above average
**Low value** (0-49): Below average deal size

#### 4. Historical Pattern Score (15% weight)

Compares the deal to similar past opportunities:
- **Similar deals won**: Number of similar deals you've closed
- **Similar deals lost**: Number of similar deals that didn't close
- **Win rate for similar deals**: Success rate with comparable opportunities
- **Pattern matching**: AI-identified similarities to successful deals

**Strong patterns** (80-100): Similar to many won deals
**Moderate patterns** (50-79): Mixed historical results
**Weak patterns** (0-49): Similar to lost deals or no historical data

#### 5. Stage Probability (10% weight)

Based on the current pipeline stage:
- **Stage probability**: Configured probability for current stage
- **Historical conversion**: Actual conversion rate from this stage
- **Stage-specific factors**: Stage characteristics and requirements

**High probability stage** (80-100): Late-stage deals (proposal, negotiation)
**Medium probability** (50-79): Mid-stage deals (qualification, demo)
**Low probability** (0-49): Early-stage deals (prospecting, initial contact)

### Interpreting Deal Scores

| Score Range | Priority | Action Required |
|-------------|----------|-----------------|
| **90-100** | Critical | Close this week - high win probability, immediate focus |
| **80-89** | High | Prioritize - strong opportunity, maintain momentum |
| **70-79** | Medium-High | Monitor closely - good potential, needs consistent engagement |
| **60-69** | Medium | Standard attention - typical deal, follow normal process |
| **50-59** | Medium-Low | Needs improvement - address weaknesses in score factors |
| **40-49** | Low | At risk - significant concerns, may not close |
| **0-39** | Very Low | Consider disqualifying - poor fit or lost opportunity |

### Viewing Deal Scores

#### On Lead Detail Page

1. Navigate to a lead/opportunity
2. View the **Deal Score Badge** in the header section
3. The badge shows:
   - Overall score (0-100)
   - Win probability percentage
   - Priority indicator (High/Medium/Low)

4. Click the badge to expand score details:
   - Breakdown of all five score components
   - Specific factors influencing each component
   - Recommendations for improving the score
   - Score trend (increasing/decreasing/stable)

#### In Lead List View

Deal scores appear as badges on lead cards in the pipeline view:
- Color-coded by priority (green=high, yellow=medium, red=low)
- Sort leads by score to prioritize your efforts
- Filter leads by minimum score threshold

#### Top Scored Leads View

1. Navigate to **Leads** > **Forecasts** > **Top Deals**
2. View your highest-scoring opportunities
3. Features:
   - Ranked list of deals by score
   - Quick access to lead details
   - Score breakdowns for each deal
   - Filter by minimum score or win probability

### Calculating Deal Scores

Deal scores are automatically calculated:

- **Daily Updates**: Scores recalculate automatically every night at 2:00 AM
- **Manual Calculation**: Trigger immediate recalculation on the lead detail page
- **On Demand**: Click **Calculate Score** to refresh score instantly

**When to manually recalculate:**
- After a significant activity (important meeting, contract sent)
- After updating deal value or expected close date
- After moving to a new pipeline stage
- When you want current information for decision-making

### Improving Deal Scores

To improve a low deal score:

1. **Increase Engagement**
   - Schedule meetings or calls
   - Send personalized emails
   - Complete demos or presentations
   - Log all activities in the CRM

2. **Accelerate Velocity**
   - Move deal to next stage when appropriate
   - Set realistic expected close dates
   - Remove blockers and objections
   - Maintain regular contact cadence

3. **Validate Value**
   - Confirm deal value is accurate
   - Ensure value matches customer budget
   - Consider upsell opportunities
   - Adjust value if necessary

4. **Learn from History**
   - Review similar won deals for patterns
   - Apply successful tactics from past wins
   - Avoid patterns that led to losses
   - Adapt your approach based on insights

5. **Advance Stage**
   - Progress deal through pipeline when qualified
   - Complete stage-specific requirements
   - Move from low-probability to high-probability stages
   - Don't rush - ensure deal is truly qualified

---

## Using Forecasts

Forecasts help you predict revenue for upcoming time periods based on your pipeline and historical performance.

### Generating a Forecast

#### Creating Your First Forecast

1. **Navigate to Forecasts**
   - Go to **Leads** > **Forecasts**
   - Click **Generate Forecast** button

2. **Configure Forecast Parameters**
   - **Period Type**: Select week, month, or quarter
   - **Period Start Date**: Choose start date (defaults to current period)
   - **User**: Select yourself (default) or team member (managers only)
   - **Team**: Optionally include team ID for team forecasts

3. **Generate Forecast**
   - Click **Generate**
   - System analyzes your pipeline and calculates forecast
   - Results appear in dashboard

4. **Review Forecast**
   - View forecast value, confidence score, and scenarios
   - Review deal breakdown and assumptions
   - Access recommendations and insights

### Understanding Forecast Values

Each forecast includes multiple values:

#### Forecast Value (Pipeline Total)
- **What it is**: Sum of all deal values in your pipeline for the period
- **Calculation**: Total of all open deals expected to close in period
- **Use case**: Upper bound estimate, "if everything closes"

#### Weighted Forecast (Most Likely)
- **What it is**: Probability-adjusted revenue prediction
- **Calculation**: Sum of (deal_value × stage_probability) for all deals
- **Use case**: Most realistic forecast, best for planning
- **Example**: $100K deal at 60% stage probability = $60K weighted value

#### Best Case Scenario
- **What it is**: Optimistic forecast assuming high success rate
- **Calculation**: All deals close at full value
- **Use case**: Maximum potential revenue, best-case planning
- **Note**: Same as Forecast Value (pipeline total)

#### Worst Case Scenario
- **What it is**: Conservative forecast based on historical performance
- **Calculation**: Applies historical conversion rates and conservative adjustments
- **Use case**: Risk management, conservative planning
- **Note**: Lower than weighted forecast, accounts for typical losses

### Confidence Score

Each forecast includes a confidence score (0-100) indicating prediction reliability:

- **90-100**: Very High Confidence
  - Large sample of historical data
  - Consistent pipeline and conversion rates
  - Mature forecasting model

- **70-89**: High Confidence
  - Good historical data available
  - Stable performance patterns
  - Reliable predictions

- **50-69**: Medium Confidence
  - Moderate historical data
  - Some variability in results
  - Reasonable predictions with monitoring

- **30-49**: Low Confidence
  - Limited historical data
  - High variability in performance
  - Use with caution, supplement with judgment

- **0-29**: Very Low Confidence
  - Insufficient data for reliable forecast
  - New pipeline or significant changes
  - Treat as rough estimate only

**Factors affecting confidence:**
- Amount of historical data available
- Consistency of past performance
- Pipeline coverage (deals vs. quota)
- Data quality and completeness
- Forecast accuracy trends

### Forecast Metadata

Each forecast includes additional context:

- **Total Leads**: Number of opportunities in the forecast
- **Pipeline Coverage**: Ratio of pipeline value to quota/target
- **Average Deal Size**: Mean value of deals in forecast
- **Calculation Method**: Algorithm used (weighted_probability)
- **Generated At**: Timestamp of forecast creation

### Viewing Your Forecasts

#### Forecast Dashboard

The main forecast dashboard shows:

1. **Current Period Forecast**
   - Highlighted card showing active period
   - Weighted forecast prominently displayed
   - Quick access to scenario values
   - Confidence indicator

2. **Forecast List**
   - All your forecasts ordered by period
   - Filter by period type (week/month/quarter)
   - Search by date range
   - Sort by date, value, or confidence

3. **Quick Stats**
   - Total forecasts generated
   - Average confidence score
   - Forecast accuracy rate
   - Recent trend (up/down/stable)

#### Forecast Detail View

Click any forecast to view details:

1. **Summary Section**
   - All forecast values and scenarios
   - Confidence score with explanation
   - Period information
   - Generation timestamp

2. **Deal Breakdown**
   - List of all deals included in forecast
   - Deal scores and win probabilities
   - Stage and value information
   - Expected close dates

3. **Assumptions & Factors**
   - Historical conversion rates used
   - Stage probabilities applied
   - Average deal size comparison
   - Any adjustments or overrides

4. **Recommendations**
   - AI-generated insights
   - Deal prioritization suggestions
   - Risk assessments
   - Actions to improve forecast

### Managing Forecasts

#### Regenerating Forecasts

Update a forecast when pipeline changes significantly:

1. Go to forecast detail page
2. Click **Regenerate**
3. System recalculates with current pipeline data
4. New forecast replaces old one (history preserved)

**When to regenerate:**
- Major deals added or removed
- Multiple stage changes
- Deal values updated significantly
- Weekly or bi-weekly cadence for active periods

#### Comparing Forecasts

Compare forecasts across time periods:

1. Navigate to **Forecasts** > **Compare**
2. Select 2-10 forecasts to compare
3. View side-by-side comparison:
   - Forecast values trending up/down
   - Confidence score changes
   - Deal count variations
   - Scenario evolution

#### Exporting Forecasts

Export forecast data for external analysis:

1. View any forecast or forecast list
2. Click **Export** button
3. Choose format: CSV, Excel, or PDF
4. Download includes all visible data and metadata

---

## Scenario Modeling

Scenario modeling helps you understand potential outcomes and plan for different situations.

### Understanding Scenarios

The system provides three forecast scenarios:

#### Weighted Scenario (Most Likely)

**Purpose**: Your most realistic forecast for planning

**Calculation**:
- Each deal is multiplied by its stage probability
- Deal scores influence weighting
- Historical conversion rates applied
- Sum of all weighted deal values

**Example**:
```
Deal A: $100K at 60% stage = $60K
Deal B: $50K at 40% stage = $20K
Deal C: $80K at 80% stage = $64K
Weighted Forecast: $144K
```

**Use this for**:
- Quota tracking and attainment
- Revenue planning and budgeting
- Sales meetings and reporting
- Performance evaluation

#### Best Case Scenario

**Purpose**: Maximum revenue potential, optimistic view

**Calculation**:
- All deals close at full value
- 100% close rate assumed
- No discounting or reduction
- Sum of all pipeline deal values

**Example**:
```
Deal A: $100K at 100% = $100K
Deal B: $50K at 100% = $50K
Deal C: $80K at 100% = $80K
Best Case: $230K
```

**Use this for**:
- Understanding upside potential
- Capacity planning (if everything closes)
- Motivational targets
- Resource allocation decisions

#### Worst Case Scenario

**Purpose**: Conservative estimate for risk management

**Calculation**:
- Historical conversion rates applied
- Pessimistic adjustments for risk
- Accounts for typical loss rates
- Discounts for uncertain deals

**Example**:
```
Historical conversion: 55%
Deal A: $100K × 55% = $55K
Deal B: $50K × 55% = $27.5K
Deal C: $80K × 55% = $44K
Worst Case: $126.5K
```

**Use this for**:
- Conservative planning
- Risk assessment
- Minimum revenue expectations
- Contingency planning

### Accessing Scenario Modeling

1. **Navigate to Forecasts**
   - Go to **Leads** > **Forecasts**
   - Click **Scenario Modeling** tab

2. **View Current Scenarios**
   - Scenarios auto-generate with each forecast
   - View weighted, best case, worst case
   - See deal-by-deal breakdown

3. **Interactive Modeling** (Advanced)
   - Adjust stage probabilities
   - Modify deal values
   - Test "what if" scenarios
   - See real-time forecast updates

### Scenario Analysis Features

#### Upside/Downside Analysis

The scenario view includes:

- **Upside Potential**: Difference between best case and weighted forecast
- **Downside Risk**: Difference between weighted and worst case
- **Total Spread**: Range from worst to best case
- **Risk/Reward Ratio**: Upside vs. downside comparison

**Example**:
```
Best Case: $230K
Weighted: $144K
Worst Case: $126.5K

Upside Potential: $86K (59%)
Downside Risk: $17.5K (12%)
Total Spread: $103.5K
Risk/Reward: 4.9:1 (favorable)
```

#### Deal Breakdown by Scenario

View which deals contribute most to each scenario:

- **High-Impact Deals**: Large deals in best case
- **Likely Closers**: Deals weighted heavily in weighted forecast
- **Risk Deals**: Deals that might not close (excluded from worst case)

#### Scenario Recommendations

AI-generated insights for each scenario:

**Best Case Recommendations**:
- "Focus on closing Deal A ($100K) to maximize revenue"
- "You have 5 high-value deals - strong upside potential"

**Weighted Case Recommendations**:
- "On track to meet 92% of quota based on current pipeline"
- "Need 3 more qualified deals to reach 100% quota"

**Worst Case Recommendations**:
- "Worst case covers 80% of quota - strong position"
- "Add more top-of-funnel leads to reduce risk"

### Using Scenarios for Planning

#### Monthly Planning

1. **Start of Month**:
   - Generate forecast for the month
   - Review weighted forecast for target setting
   - Check worst case for minimum expectations
   - Identify gap to quota

2. **Mid-Month**:
   - Regenerate forecast with updated pipeline
   - Compare new vs. old scenarios
   - Adjust strategy based on trends
   - Focus on high-probability deals

3. **End of Month**:
   - Final forecast generation
   - Compare forecast vs. actual
   - Analyze variance
   - Apply learnings to next month

#### Quarterly Business Reviews

1. **Review Historical Scenarios**:
   - How did forecasts evolve over quarter?
   - Were you consistently optimistic or pessimistic?
   - Did best/worst case scenarios help with planning?

2. **Analyze Accuracy**:
   - Compare weighted forecasts to actual results
   - Identify patterns in over/under forecasting
   - Adjust future scenario expectations

3. **Strategic Planning**:
   - Use scenarios for next quarter planning
   - Set realistic targets based on scenarios
   - Plan for best and worst case outcomes

---

## Forecast Accuracy Tracking

Tracking forecast accuracy helps you improve predictions over time and build confidence in your forecasting process.

### How Accuracy Tracking Works

#### Automatic Tracking

The system automatically tracks forecast accuracy:

1. **Period End**: When a forecast period ends (week/month/quarter)
2. **Actual Calculation**: System calculates actual closed revenue
3. **Comparison**: Compares forecast to actual results
4. **Variance Calculation**: Determines difference and percentage variance
5. **Record Creation**: Creates forecast actual record
6. **Metrics Update**: Updates your overall accuracy metrics

#### Accuracy Metrics

For each forecast with actual results, you'll see:

- **Forecast Value**: Original forecasted amount
- **Actual Value**: Revenue that actually closed
- **Variance**: Difference (actual - forecast)
- **Variance %**: Percentage difference
- **Accuracy**: How close forecast was to actual (100% - variance%)

### Viewing Accuracy Reports

#### Forecast Accuracy Dashboard

Access via **Leads** > **Forecasts** > **Accuracy**

**Overview Metrics**:
- **Total Forecasts**: Number of completed forecast periods
- **Average Accuracy**: Mean accuracy across all forecasts
- **Average Variance**: Mean dollar variance
- **Accuracy Rate**: Percentage of forecasts within acceptable range (±10%)

**Breakdown**:
- **Over-Forecasted Count**: How often you predicted too high
- **Under-Forecasted Count**: How often you predicted too low
- **Accurate Count**: Forecasts within ±10% of actual

**Trend Chart**:
- Visual representation of forecast vs. actual over time
- Identify patterns and improvements
- See accuracy trending up or down

#### Individual Forecast Accuracy

On each completed forecast:

1. **Actuals Section**: Shows actual results
2. **Variance Analysis**: Detailed breakdown of differences
3. **Contributing Factors**: What caused variance (deals that didn't close, unexpected wins)
4. **Lessons Learned**: AI-generated insights on forecast accuracy

### Interpreting Accuracy Metrics

#### Accuracy Percentage

| Accuracy | Rating | Interpretation |
|----------|--------|----------------|
| **95-100%** | Excellent | Nearly perfect forecast, very reliable |
| **90-94%** | Very Good | Highly accurate, minor variance |
| **85-89%** | Good | Solid forecasting, acceptable variance |
| **75-84%** | Fair | Room for improvement, moderate variance |
| **Below 75%** | Poor | Significant variance, needs attention |

#### Variance Direction

**Positive Variance** (Actual > Forecast):
- You under-forecasted
- Closed more than expected
- Good problem to have!
- Reasons: Unexpected wins, deals accelerated, larger deal sizes

**Negative Variance** (Actual < Forecast):
- You over-forecasted
- Closed less than expected
- Need to understand why
- Reasons: Deals slipped, losses, overestimated probabilities

**Zero Variance**:
- Perfect forecast (rare!)
- Very high forecasting skill
- Good data and process

#### Consistency Patterns

**Consistently Over-Forecasting**:
- You're too optimistic
- Adjust stage probabilities down
- Be more conservative with deal inclusion
- Qualify deals more rigorously

**Consistently Under-Forecasting**:
- You're too pessimistic
- Adjust stage probabilities up
- Include more deals in forecast
- Increase confidence in your pipeline

**Erratic Variance**:
- Inconsistent forecasting
- Need more stable process
- Improve data quality
- Increase forecast frequency

### Improving Forecast Accuracy

#### Short-Term Improvements

1. **Update Deal Information Regularly**
   - Keep deal values current
   - Update expected close dates
   - Move deals to correct stages
   - Remove dead deals from pipeline

2. **Increase Forecast Frequency**
   - Generate forecasts weekly instead of monthly
   - React to pipeline changes quickly
   - Build forecasting muscle memory
   - Catch issues earlier

3. **Review Deal Scores**
   - Use deal scores to validate assumptions
   - Remove low-scoring deals from forecast
   - Weight high-scoring deals more heavily
   - Let AI help with prioritization

4. **Track Reasons for Variance**
   - Document why forecasts were off
   - Identify common patterns
   - Adjust process to address issues
   - Learn from both wins and losses

#### Long-Term Improvements

1. **Historical Data Accumulation**
   - System improves with more data
   - Each completed forecast enhances predictions
   - Conversion rates become more accurate
   - Patterns emerge over time

2. **Process Refinement**
   - Establish consistent forecasting cadence
   - Standardize pipeline management
   - Define stage criteria clearly
   - Train team on best practices

3. **Stage Probability Calibration**
   - Review historical conversion by stage
   - Adjust stage probabilities to match reality
   - Different probabilities for different deal types
   - Update probabilities quarterly

4. **Pipeline Hygiene**
   - Regular pipeline reviews
   - Remove stale opportunities
   - Qualify deals properly before adding
   - Maintain accurate deal information

### Accuracy Insights

The system provides AI-generated insights on accuracy:

#### Pattern Analysis

- **"You tend to over-forecast by 15% on average"**
  - Action: Reduce stage probabilities by 15%

- **"Your Q4 forecasts are more accurate than Q1"**
  - Action: Understand what you do differently in Q4

- **"Large deals (>$100K) forecast accuracy is lower"**
  - Action: Apply extra scrutiny to large deal assumptions

#### Recommendations

Based on your accuracy history:

- **Optimistic Forecaster**: "Consider using worst-case scenario for planning"
- **Pessimistic Forecaster**: "Your forecasts are conservative - add buffer for upside"
- **Erratic Forecaster**: "Increase forecast frequency to improve consistency"
- **Accurate Forecaster**: "Excellent forecasting! Maintain current process"

### Accuracy Targets

Set accuracy targets for continuous improvement:

**Organizational Targets**:
- **Minimum Acceptable**: 80% accuracy
- **Good Performance**: 85-90% accuracy
- **Excellent Performance**: >90% accuracy

**Individual Targets**:
- New reps: 75-80% in first 6 months
- Experienced reps: 85-90%
- Top performers: >90%

**Improvement Goals**:
- Improve 5% quarter-over-quarter
- Reduce variance by 10% year-over-year
- Increase consistency (reduce variance spread)

---

## Team Forecasting

Team forecasting helps sales managers aggregate individual forecasts and plan resources across their teams.

### Accessing Team Forecasts

**Prerequisites**:
- Manager role or appropriate permissions
- Team members assigned to you
- Team members with active pipelines

**Navigation**:
1. Go to **Leads** > **Forecasts**
2. Click **Team View** tab
3. Select your team from dropdown

### Team Forecast Dashboard

#### Team Summary

Overview metrics for entire team:

- **Total Team Forecast**: Sum of all team member weighted forecasts
- **Best Case Scenario**: Combined team best case
- **Worst Case Scenario**: Combined team worst case
- **Average Confidence**: Mean confidence score across team
- **Team Quota**: If quotas configured, shows attainment percentage

#### Individual Contributions

Table showing each team member:

| Team Member | Forecast | Best Case | Worst Case | Confidence | % of Team Total |
|-------------|----------|-----------|------------|------------|----------------|
| John Doe | $150K | $180K | $90K | 85% | 25% |
| Jane Smith | $200K | $240K | $120K | 80% | 33% |
| Bob Johnson | $120K | $150K | $80K | 75% | 20% |

**Features**:
- Sort by any column
- Click member name to view their forecast details
- See relative contribution to team total
- Identify high and low performers

#### Team Performance Chart

Visual representation showing:
- Each team member's forecast as a bar
- Color-coded by confidence level
- Target/quota line (if configured)
- Team total marker

### Generating Team Forecasts

#### Aggregate Team Forecast

1. **Navigate to Team Forecasts**
   - Go to **Leads** > **Forecasts** > **Team View**

2. **Select Parameters**
   - Choose period type (week/month/quarter)
   - Select period start date
   - Select your team

3. **Generate**
   - Click **Generate Team Forecast**
   - System generates forecasts for all team members
   - Aggregates results into team forecast
   - Shows individual and combined results

#### Individual Member Forecasts

Generate forecast for a specific team member:

1. In Team View, click team member name
2. Click **Generate Forecast**
3. Configure parameters for that member
4. View individual results
5. Return to team view to see updated team total

### Team Forecast Analysis

#### Quota Attainment

If quotas are configured:

- **Team Quota**: Total team target
- **Forecasted Attainment**: Team forecast / quota
- **Gap to Quota**: Amount needed to reach 100%
- **Members at Risk**: Team members below 80% quota attainment

**Example**:
```
Team Quota: $500K
Team Forecast: $470K
Attainment: 94%
Gap: $30K (need 2 more deals at $15K average)
```

#### Distribution Analysis

Understand how forecast is distributed:

- **Top Contributors**: Members contributing most to team total
- **Bottom Contributors**: Members who may need support
- **Balance**: Is forecast evenly distributed or concentrated?
- **Risk Assessment**: What if top contributor doesn't deliver?

#### Team Confidence

Aggregate confidence metrics:

- **High Confidence Members**: Count of members with >80% confidence
- **Low Confidence Members**: Members with <60% confidence (need support)
- **Average Confidence**: Team-wide confidence score
- **Confidence Trend**: Is team confidence improving or declining?

### Team Forecast Comparison

Compare forecasts across time periods:

1. **Select Date Range**
   - Choose start and end dates
   - View multiple forecast periods

2. **Trend Analysis**
   - Is team forecast growing or declining?
   - Which members are trending up/down?
   - Seasonal patterns
   - Growth rate

3. **Period Comparison**
   - Compare current month to previous month
   - Compare to same period last year
   - Identify changes in team composition or performance

### Managing Team Performance

#### Identifying At-Risk Members

Red flags in team forecasts:

- **Low Forecast Values**: Significantly below average
- **Low Confidence Scores**: <60% confidence
- **Low Deal Scores**: Most deals scoring <50
- **Declining Trends**: Forecast decreasing month-over-month
- **Low Pipeline Coverage**: Pipeline value < 2x quota

**Actions for at-risk members**:
- One-on-one coaching sessions
- Pipeline review and cleanup
- Lead assignment support
- Training on specific skills
- Collaborative deal reviews

#### Supporting High Performers

Recognize and leverage top performers:

- **High Forecast + High Confidence**: Likely to exceed quota
- **Consistent Accuracy**: Historical forecasts match actuals
- **High Deal Scores**: Prioritizing effectively
- **Best Practices**: Learn from their approach

**Actions for high performers**:
- Share best practices with team
- Assign stretch goals
- Consider for mentoring role
- Reward and recognize success

#### Team Coaching Opportunities

Use team forecasts to drive coaching:

1. **Pipeline Reviews**
   - Review each member's top deals
   - Discuss deal scores and strategies
   - Identify support needed
   - Share insights across team

2. **Forecast Accuracy Review**
   - Compare past forecasts to actuals
   - Identify patterns in over/under forecasting
   - Calibrate stage probabilities together
   - Set team accuracy goals

3. **Deal Prioritization**
   - Review low-scoring deals
   - Discuss qualification criteria
   - Consider deal removal or requalification
   - Focus efforts on high-probability deals

### Team Forecasting Best Practices

#### Regular Cadence

Establish consistent team forecasting rhythm:

- **Weekly**: Quick check-ins, update forecasts for current week/month
- **Monthly**: Full team forecast review, accuracy analysis, next month planning
- **Quarterly**: Strategic review, quota setting, resource allocation

#### Standard Process

Create team forecasting standards:

1. **Timing**: All forecasts generated by specific day/time
2. **Review**: Manager reviews each member's forecast
3. **Discussion**: One-on-one or team review meeting
4. **Adjustments**: Make changes based on discussion
5. **Commitment**: Team members commit to forecast
6. **Tracking**: Monitor actual results vs. forecast

#### Team Forecast Meetings

Run effective forecast meetings:

**Agenda**:
1. Review team aggregate forecast
2. Review individual forecasts
3. Discuss significant deals
4. Identify risks and opportunities
5. Assign action items
6. Set forecast for next period

**Meeting Tips**:
- Keep meetings focused (30-60 minutes)
- Use forecast dashboard as visual aid
- Encourage honest assessment
- Focus on actions, not blame
- Celebrate successes

#### Transparency and Trust

Build forecast culture:

- **No Sandbagging**: Encourage accurate forecasts, not conservative
- **No Gaming**: Reward accuracy, not beating forecast
- **Safe Environment**: Support members who miss forecast
- **Learn Together**: Share lessons from variance
- **Data-Driven**: Use system insights, not gut feelings

---

## Analytics and Insights

The forecasting system includes advanced analytics to help you understand trends, patterns, and opportunities.

### Forecast Analytics Dashboard

Access via **Leads** > **Forecasts** > **Analytics**

#### Trend Analysis

**Revenue Trends**:
- Monthly revenue over time (won deals)
- Forecast trends over time
- Compare forecast vs. actual trends
- Identify seasonality and patterns

**Performance Metrics**:
- Win rate trends
- Average deal size trends
- Deal velocity trends
- Conversion rate by stage over time

**Insights**:
- "Revenue trending upward 16% - strong growth"
- "Q4 consistently outperforms Q1 - seasonal pattern"
- "Win rate improving 5% quarter-over-quarter"

#### Scenario Analysis

**What-If Scenarios**:
- Interactive scenario modeling
- Adjust variables to see impact:
  - Change stage probabilities
  - Modify deal values
  - Add/remove deals
  - Adjust close dates

**Risk Analysis**:
- Pipeline coverage analysis
- Deal concentration risk
- Stage distribution
- Timeline risk (deals bunched at end of period)

**Opportunity Analysis**:
- Upside potential identification
- Best deals to focus on
- Deals needing attention
- Quick wins vs. strategic opportunities

#### Comparison Analytics

**Forecast Comparison**:
- Compare multiple forecast periods
- Side-by-side scenario comparison
- Variance analysis over time
- Accuracy trends

**Example**:
```
Period: Jan vs Feb vs Mar

Forecast:
Jan: $150K (Actual: $145K, 97% accuracy)
Feb: $175K (Actual: $165K, 94% accuracy)
Mar: $200K (Pending)

Trend: Growing 16% month-over-month
Accuracy: Improving (was 85% in Q4, now 95% in Q1)
```

### AI-Generated Insights

The system uses AI to generate actionable insights:

#### Deal Prioritization

**Recommendations**:
- "Focus on Deal #42 - high score (85), closing this week"
- "Deal #37 needs attention - score dropped from 75 to 55"
- "5 deals stalled in qualification stage - review and progress"

#### Pipeline Health

**Assessments**:
- "Strong pipeline - coverage at 2.5x quota"
- "Pipeline concentration risk - top 3 deals are 60% of forecast"
- "Need more early-stage deals - weak next quarter pipeline"

#### Forecast Confidence

**Feedback**:
- "High confidence forecast - consistent with historical patterns"
- "Moderate confidence - limited historical data for this period"
- "Confidence increasing - recent forecast accuracy improved"

#### Performance Patterns

**Observations**:
- "You close more deals on Fridays - timing pattern identified"
- "Large deals (>$100K) take 45 days longer than average"
- "Demo stage has 75% conversion - strong qualification"

### Historical Analysis

#### Conversion Rates

View historical conversion rates:

- **By Stage**: Conversion rate from each pipeline stage
- **By Deal Size**: Win rate for small/medium/large deals
- **By Source**: Conversion by lead source
- **By User**: Team member performance comparison

**Use cases**:
- Calibrate stage probabilities
- Identify qualification issues
- Focus on best lead sources
- Benchmark performance

#### Win/Loss Analysis

Understand what you won and lost:

- **Total Wins**: Number and value of won deals
- **Total Losses**: Number and value of lost deals
- **Win Rate**: Percentage of deals won
- **Average Win Size**: Mean value of won deals
- **Average Loss Size**: Mean value of lost deals

**Loss Reasons** (if tracked):
- Pricing issues
- Competitor wins
- No decision
- Feature gaps
- Timeline issues

#### Velocity Metrics

Analyze deal progression speed:

- **Average Days in Pipeline**: Total time from creation to close
- **Average Days per Stage**: Time spent in each stage
- **Velocity Trends**: Is velocity improving or slowing?
- **Bottleneck Stages**: Where do deals get stuck?

**Use cases**:
- Improve sales process
- Identify bottlenecks
- Set realistic close date expectations
- Improve forecast accuracy

### Advanced Analytics Features

#### Custom Date Ranges

Analyze any time period:
- Last 30/60/90 days
- Quarter-to-date
- Year-to-date
- Custom date range

#### Filtering and Segmentation

Filter analytics by:
- **User**: Individual or team
- **Pipeline**: Specific pipelines
- **Deal Size**: Small/medium/large deals
- **Source**: Lead sources
- **Stage**: Current or historical stage

#### Export and Reporting

Export analytics data:

1. **Format Options**: CSV, Excel, PDF
2. **Content**: Charts, tables, and metrics
3. **Customization**: Select specific sections
4. **Scheduling**: Automated weekly/monthly reports (admin)

### Using Insights Effectively

#### Daily Actions

Start each day:
1. Review top-scored deals
2. Check AI recommendations
3. Focus on high-priority opportunities
4. Address at-risk deals

#### Weekly Review

Every week:
1. Review forecast vs. actual progress
2. Regenerate forecast with updated pipeline
3. Analyze variance from previous week
4. Adjust priorities based on insights

#### Monthly Analysis

Monthly review:
1. Full accuracy analysis
2. Win/loss review
3. Pipeline health assessment
4. Next month planning with insights
5. Apply learnings to process

#### Quarterly Planning

Quarterly business review:
1. Long-term trend analysis
2. Forecast accuracy over quarter
3. Pipeline development review
4. Strategic adjustments
5. Target setting for next quarter

---

## Best Practices

### Deal Scoring Best Practices

#### Keep Deal Information Updated

- **Update regularly**: Review and update deals at least weekly
- **Accurate values**: Ensure deal values reflect current reality
- **Current stages**: Move deals to correct stages promptly
- **Expected close dates**: Keep close dates realistic and current
- **Log activities**: Record all meetings, calls, and emails

#### Use Scores to Prioritize

- **Daily focus**: Check top-scored deals each morning
- **Weekly review**: Review all deal scores in pipeline
- **Action on low scores**: Address issues with low-scoring deals
- **Celebrate high scores**: Recognize and close high-probability deals

#### Improve Engagement

- **Regular contact**: Maintain consistent communication cadence
- **Multiple touchpoints**: Use various channels (email, phone, demo)
- **Stakeholder mapping**: Engage with all decision-makers
- **Value-added interactions**: Every contact should provide value

#### Accelerate Velocity

- **Remove blockers**: Address objections and concerns quickly
- **Clear next steps**: Every interaction should have defined next action
- **Timeline management**: Set and manage expectations on timeline
- **Stage progression**: Move deals forward systematically

### Forecasting Best Practices

#### Establish a Forecasting Cadence

- **Weekly forecasts**: Generate new forecast each week
- **Consistent timing**: Same day/time each week
- **Regular reviews**: Schedule forecast review meetings
- **Update as needed**: Regenerate when pipeline changes significantly

#### Use Weighted Forecasts for Planning

- **Most realistic**: Weighted forecast is most accurate for planning
- **Avoid optimism**: Don't plan based on best case
- **Risk management**: Consider worst case for contingency planning
- **Communicate clearly**: Ensure stakeholders understand which forecast you're using

#### Maintain Pipeline Hygiene

- **Regular cleanup**: Remove dead/stale deals monthly
- **Qualify rigorously**: Only include qualified opportunities
- **Accurate data**: Keep all deal information current
- **Realistic stages**: Don't move deals forward prematurely

#### Track and Learn from Accuracy

- **Review variance**: Analyze why forecasts were off
- **Identify patterns**: Look for systematic biases
- **Adjust process**: Make changes based on learnings
- **Set accuracy goals**: Strive to improve quarter-over-quarter

### Team Forecasting Best Practices

#### Create Standard Process

- **Consistent methodology**: All team members use same approach
- **Clear timing**: Everyone knows when forecasts are due
- **Review process**: Manager reviews each forecast
- **Accountability**: Team members commit to their forecasts

#### Foster Forecast Accuracy Culture

- **Reward accuracy**: Recognize accurate forecasters
- **No sandbagging**: Discourage conservative forecasting
- **Safe environment**: Support those who miss forecast
- **Learn together**: Share lessons from variance

#### Use Team Forecasts for Coaching

- **Pipeline reviews**: Regular one-on-ones using forecast data
- **Deal coaching**: Review high-value or at-risk deals
- **Best practice sharing**: Learn from top performers
- **Performance management**: Use forecasts to identify coaching needs

### Data Quality Best Practices

#### Maintain Complete Deal Information

- **Required fields**: Ensure all required fields are filled
- **Deal value**: Accurate and current
- **Expected close date**: Realistic timeline
- **Contact information**: Key stakeholders identified
- **Stage alignment**: Deal is in correct stage

#### Regular Data Reviews

- **Weekly**: Quick scan for obvious issues
- **Monthly**: Comprehensive data quality review
- **Quarterly**: Deep dive into data patterns
- **Annual**: Full data audit and cleanup

#### Consistent Data Entry

- **Naming conventions**: Use consistent names and terminology
- **Field definitions**: Understand what each field represents
- **Training**: Ensure all users understand data standards
- **Templates**: Use deal templates for consistency

### Strategic Best Practices

#### Align Forecasts with Goals

- **Quota alignment**: Know your targets
- **Gap analysis**: Understand shortfall to quota
- **Action planning**: Define actions to close gaps
- **Resource allocation**: Use forecasts to allocate resources

#### Communicate Effectively

- **Regular updates**: Share forecast status with stakeholders
- **Clear explanations**: Help others understand your forecast
- **Highlight changes**: Call out significant forecast changes
- **Set expectations**: Be clear about confidence and risk

#### Continuous Improvement

- **Regular retrospectives**: What's working? What's not?
- **Process refinement**: Adjust based on learnings
- **Stay current**: Keep up with new features and best practices
- **Share knowledge**: Help others improve their forecasting

---

## Troubleshooting

### Common Issues and Solutions

#### Issue: Deal Score Not Appearing

**Possible Causes**:
- Score not yet calculated
- Deal missing required information
- Recent lead, score calculating
- System error

**Solutions**:
1. Click **Calculate Score** on lead detail page
2. Ensure lead has required fields: value, stage, expected close date
3. Wait for daily auto-calculation (2:00 AM)
4. Check that lead is active (status = 1)
5. Verify lead has activities or engagement data
6. Contact administrator if issue persists

#### Issue: Score Seems Incorrect

**Possible Causes**:
- Outdated information
- Missing activity data
- Incorrect stage or value
- Score not recalculated after updates

**Solutions**:
1. Review score breakdown to understand factors
2. Verify all lead information is current
3. Check that activities are logged in CRM
4. Recalculate score manually
5. Update deal information and recalculate
6. Review historical pattern matches

#### Issue: Forecast Confidence is Low

**Possible Causes**:
- Insufficient historical data
- New pipeline or user
- High variability in performance
- Data quality issues

**Solutions**:
1. Continue generating forecasts - confidence improves with data
2. Ensure consistent pipeline management
3. Improve data quality (complete deal information)
4. Generate forecasts more frequently
5. Review and clean up pipeline regularly
6. For new users: Confidence will improve over 3-6 months

#### Issue: Forecast Value Seems Wrong

**Possible Causes**:
- Incorrect deal values in pipeline
- Deals in wrong stages
- Stage probabilities misconfigured
- Dead deals not removed from pipeline

**Solutions**:
1. Review all deals included in forecast
2. Verify deal values are accurate
3. Move deals to correct stages
4. Remove stale/dead opportunities
5. Check stage probability settings
6. Regenerate forecast after corrections

#### Issue: Forecast Accuracy is Poor

**Possible Causes**:
- Inconsistent forecasting process
- Poor pipeline management
- Incorrect stage probabilities
- Deals slipping or unexpected losses

**Solutions**:
1. Review variance to understand causes
2. Improve pipeline hygiene
3. Update deals more frequently
4. Adjust stage probabilities based on historical data
5. Qualify deals more rigorously
6. Generate forecasts more frequently
7. Track reasons for variance and adjust process

#### Issue: Team Forecasts Not Showing

**Possible Causes**:
- Insufficient permissions
- Team not configured
- No team members assigned
- Team members have no forecasts

**Solutions**:
1. Verify you have manager permissions
2. Check that team structure is set up
3. Ensure team members are assigned to you
4. Generate forecasts for team members
5. Contact administrator to verify permissions
6. Check team configuration in settings

#### Issue: Cannot Generate Forecast

**Possible Causes**:
- No deals in pipeline
- All deals lack required information
- Insufficient historical data
- System error or timeout

**Solutions**:
1. Ensure you have active deals in pipeline
2. Verify deals have values and expected close dates
3. Check that deals are in valid stages
4. Review error message for specific issue
5. Try generating for different period type
6. Contact administrator if error persists

#### Issue: Deal Scores Not Updating

**Possible Causes**:
- Daily job not running
- System issue
- Calculation error
- Cache issue

**Solutions**:
1. Manually calculate score on affected deals
2. Check system logs for job errors
3. Verify scheduled job is running (admin)
4. Clear application cache (admin)
5. Check for system updates or maintenance
6. Contact administrator

### Performance Issues

#### Slow Forecast Generation

**Possible Causes**:
- Large pipeline (many deals)
- Complex historical analysis
- Server load
- Database performance

**Solutions**:
1. Be patient - large pipelines take time
2. Generate forecasts during off-peak hours
3. Clean up pipeline to reduce deal count
4. Contact administrator about performance optimization
5. Consider generating forecasts for shorter periods (week vs. quarter)

#### Analytics Loading Slowly

**Possible Causes**:
- Large dataset
- Complex queries
- Multiple users accessing simultaneously
- Cache not optimized

**Solutions**:
1. Use date filters to reduce dataset
2. Access analytics during off-peak times
3. Clear browser cache
4. Contact administrator about performance
5. Export data for offline analysis

### Data Issues

#### Missing Historical Data

**Possible Causes**:
- New CRM installation
- Historical data not migrated
- Data cleanup removed history
- Historical analysis job not run

**Solutions**:
1. Contact administrator about historical data
2. Run historical data seed command (admin)
3. Wait for historical analysis to accumulate
4. Import historical data if available
5. Understand forecasts will improve with time

#### Incorrect Conversion Rates

**Possible Causes**:
- Historical analysis needs refresh
- Data quality issues in past deals
- Recent changes not reflected
- Stage definitions changed

**Solutions**:
1. Request historical data refresh (admin)
2. Review past deal data quality
3. Manually review conversion rates by stage
4. Update stage probabilities to match actual rates
5. Wait for weekly historical refresh job

### Getting Help

If you encounter issues not covered here:

#### Check Documentation

- **User Guide**: This document
- **API Documentation**: For technical details
- **Knowledge Base**: Search for related articles
- **Video Tutorials**: Visual walkthroughs

#### Contact Support

When contacting support, provide:
- **Detailed description**: What's happening vs. expected behavior
- **Steps to reproduce**: How to recreate the issue
- **Screenshots**: Visual evidence of the problem
- **User and deal IDs**: Specific records affected
- **Error messages**: Any errors displayed

#### Administrator Resources

For system administrators:
- **System logs**: Check application logs for errors
- **Database queries**: Verify data integrity
- **Job status**: Ensure scheduled jobs are running
- **Configuration**: Review system settings
- **Performance monitoring**: Check system performance metrics

---

## Appendix

### Glossary

- **Deal Score**: AI-generated rating (0-100) indicating likelihood of closing a deal
- **Win Probability**: Percentage chance of successfully closing a deal
- **Forecast**: Prediction of revenue for a future time period
- **Weighted Forecast**: Probability-adjusted revenue prediction (most realistic)
- **Best Case Scenario**: Optimistic forecast assuming all deals close
- **Worst Case Scenario**: Conservative forecast based on historical conversion
- **Confidence Score**: Reliability rating for a forecast (0-100)
- **Pipeline Coverage**: Ratio of pipeline value to quota/target
- **Forecast Accuracy**: How close forecast was to actual results
- **Variance**: Difference between forecast and actual results
- **Engagement Score**: Component measuring interaction level with prospect
- **Velocity Score**: Component measuring deal progression speed
- **Value Score**: Component measuring deal size relative to average
- **Historical Pattern Score**: Component based on similar past deals
- **Stage Probability**: Likelihood of closing from current pipeline stage

### Scoring Methodology Reference

#### Deal Score Calculation

```
Overall Score = (Engagement × 0.30) + (Velocity × 0.25) + (Value × 0.20) +
                (Historical Pattern × 0.15) + (Stage Probability × 0.10)
```

**Weights**:
- Engagement: 30%
- Velocity: 25%
- Value: 20%
- Historical Pattern: 15%
- Stage Probability: 10%

#### Win Probability Calculation

Combines:
- Stage probability (40%)
- Historical win rate for similar deals (30%)
- Deal score (20%)
- Velocity metrics (10%)

#### Forecast Calculations

**Weighted Forecast**:
```
Weighted Forecast = Σ (Deal Value × Stage Probability × Deal Score Factor)
```

**Best Case**:
```
Best Case = Σ (All Open Deal Values)
```

**Worst Case**:
```
Worst Case = Σ (Deal Value × Historical Conversion Rate × Risk Factor)
```

### Field Reference

#### Deal Score Fields
- `score`: Overall composite score (0-100)
- `win_probability`: Predicted win percentage (0-100)
- `velocity_score`: Deal velocity component (0-100)
- `engagement_score`: Engagement component (0-100)
- `value_score`: Value component (0-100)
- `historical_pattern_score`: Historical pattern component (0-100)
- `factors`: JSON breakdown of score components
- `generated_at`: Timestamp of score calculation

#### Forecast Fields
- `forecast_value`: Total pipeline value
- `weighted_forecast`: Probability-adjusted forecast
- `best_case`: Optimistic scenario forecast
- `worst_case`: Conservative scenario forecast
- `confidence_score`: Forecast reliability (0-100)
- `period_type`: week, month, or quarter
- `period_start`: Start date of forecast period
- `period_end`: End date of forecast period
- `metadata`: Additional forecast details (JSON)

#### Forecast Actual Fields
- `forecast_id`: Related forecast
- `actual_value`: Revenue that actually closed
- `variance`: Difference (actual - forecast)
- `variance_percentage`: Percentage variance
- `closed_at`: Period end date

### Keyboard Shortcuts

**Forecast Dashboard**:
- `G` then `F`: Go to forecasts
- `N`: Generate new forecast
- `R`: Refresh current view
- `E`: Export current data

**Lead Detail**:
- `S`: View deal score details
- `C`: Calculate/recalculate score
- `F`: Add to forecast

### API Endpoints

For developers and integrators:

**Deal Scoring**:
- `GET /api/leads/{id}/score`: Get lead score
- `POST /api/leads/{id}/score/calculate`: Calculate score
- `GET /api/leads/top-scored`: Get top-scored leads

**Forecasting**:
- `GET /api/forecasts`: List forecasts
- `POST /api/forecasts/generate`: Generate forecast
- `GET /api/forecasts/{id}`: Get forecast details
- `GET /api/forecasts/accuracy`: Get accuracy metrics
- `GET /api/forecasts/team/{teamId}`: Get team forecast

**Analytics**:
- `GET /api/forecasts/analytics/trends`: Get trends
- `GET /api/forecasts/analytics/scenarios`: Get scenarios
- `GET /api/forecasts/analytics/comparison`: Compare forecasts

See [API Documentation](../api/forecasting.md) for complete details.

---

## Revision History

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | 2026-01-11 | Initial release of Sales Forecasting user guide |

---

**Need Help?** Contact your system administrator or refer to the [API Documentation](../api/forecasting.md) for technical details.
