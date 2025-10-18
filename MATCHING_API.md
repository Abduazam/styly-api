# Matching API Documentation

The Matching API provides AI-powered outfit matching functionality that helps users find complementary clothes from their wardrobe based on selected items.

## Endpoints

### 1. Find Matching Clothes
**POST** `/api/matching/find-matches`

Find clothes that match with the user's selected items.

**Request Body:**
```json
{
  "selected_clothes": [1, 2, 3],
  "use_visual_analysis": false
}
```

**Parameters:**
- `selected_clothes` (required): Array of clothe IDs that the user wants to match
- `use_visual_analysis` (optional): Boolean to use visual analysis instead of textual analysis (default: false)

**Response:**
```json
{
  "success": true,
  "message": "Matching clothes found successfully",
  "data": {
    "success": true,
    "selected_clothes": [...],
    "matching_results": {
      "matches": [
        {
          "clothe_id": 4,
          "match_score": 0.85,
          "reasoning": "Complements the color scheme",
          "style_notes": "Perfect for casual occasions",
          "clothe": {...}
        }
      ],
      "outfit_suggestions": [
        {
          "name": "Casual Day Out",
          "items": [1, 4, 5],
          "description": "A relaxed outfit perfect for weekend activities",
          "occasion": "casual",
          "confidence": 0.9,
          "clothes": [...]
        }
      ],
      "styling_tips": [
        "Try layering with a light jacket",
        "Accessorize with a simple necklace"
      ]
    },
    "total_available": 10,
    "analysis_type": "textual"
  }
}
```

### 2. Get Outfit Suggestions
**POST** `/api/matching/outfit-suggestions`

Generate complete outfit suggestions based on selected clothes.

**Request Body:**
```json
{
  "selected_clothes": [1, 2],
  "occasion": "business",
  "season": "winter"
}
```

**Parameters:**
- `selected_clothes` (required): Array of clothe IDs
- `occasion` (optional): Filter by occasion (casual, business, formal, party, athleisure, lounge)
- `season` (optional): Filter by season (spring, summer, autumn, winter, all-season)

**Response:**
```json
{
  "success": true,
  "message": "Outfit suggestions generated successfully",
  "data": {
    "success": true,
    "selected_clothes": [...],
    "matching_results": {
      "outfit_suggestions": [
        {
          "name": "Professional Look",
          "items": [1, 3, 6],
          "description": "A polished outfit for business meetings",
          "occasion": "business",
          "confidence": 0.95,
          "clothes": [...]
        }
      ]
    }
  }
}
```

### 3. Get Styling Tips
**POST** `/api/matching/styling-tips`

Get AI-generated styling advice for selected clothes.

**Request Body:**
```json
{
  "selected_clothes": [1, 2, 3]
}
```

**Response:**
```json
{
  "success": true,
  "message": "Styling tips generated successfully",
  "data": {
    "selected_clothes": [...],
    "styling_tips": [
      "This color combination works well for spring",
      "Consider adding a belt to define your waist",
      "These pieces are versatile for multiple occasions"
    ]
  }
}
```

## Analysis Types

### Textual Analysis (Default)
- Uses clothing metadata (category, occasion, season, color palette)
- Faster processing
- Good for basic matching based on attributes

### Visual Analysis
- Analyzes actual clothing images
- More accurate color and style matching
- Slower processing due to image analysis
- Better for complex style matching

## Error Handling

All endpoints return appropriate HTTP status codes:

- **200**: Success
- **422**: Validation errors (invalid clothe IDs, missing required fields)
- **500**: Server errors (AI service unavailable, processing errors)

**Error Response Format:**
```json
{
  "success": false,
  "message": "Error description",
  "data": {
    "success": false,
    "error": "Detailed error message"
  }
}
```

## Usage Examples

### Basic Matching
```bash
curl -X POST http://localhost:8000/api/matching/find-matches \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "selected_clothes": [1, 2]
  }'
```

### Visual Analysis
```bash
curl -X POST http://localhost:8000/api/matching/find-matches \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "selected_clothes": [1, 2],
    "use_visual_analysis": true
  }'
```

### Filtered Outfit Suggestions
```bash
curl -X POST http://localhost:8000/api/matching/outfit-suggestions \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "selected_clothes": [1],
    "occasion": "formal",
    "season": "winter"
  }'
```

## Notes

- All selected clothes must belong to the authenticated user
- The API excludes selected clothes from matching results
- Visual analysis requires clothing images to be available in storage
- Results include confidence scores and reasoning for transparency
- The AI considers color harmony, style compatibility, and occasion appropriateness
