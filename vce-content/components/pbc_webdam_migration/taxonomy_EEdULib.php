<?php

// topics for EEdU Lib

/*
$folders_to_search = array(
"1937835", // Produced
"2212282", // Produced Spanish SUBS
"2255081", // Shows
"2386434", // Circle Time Magazine Episodes
"2255087", // Meaningful Makeover Episodes
"1937842", // Webinars
"2687903", // AIAN Webinars
"2117519", // Coaching Companion Webinars
"2092153", // Disabilities Dialogue Webinars
"2092152", // EarlyEdU Highlight Webinars
"2582937", // Front Porch Series
"1937836", // Special Collections
"1937837", // Distinguished Lecture Series
"1937838", // Real to Reel
"2792949", // Spring Lecture Series
"2360596"  // Teacher Time
);
// "2714053" // Child Development Brain Building
*/


$folders_to_search = array(
"1937835", // Produced (Renamed to: Videos)
"2212282", // Produced Spanish SUBS (Renamed toVideos-Spanish SUBS)
"2255081", // Shows
"1937842", // Webinars
"1937836", // Special Collections
"3619311", // Interviews
"3619310" // Lectures
);


$base_name = "BROWSE";

// set a value to added to all query
// base_filter = '#WACCLib';


// topics and sub-topics

$search = array(
	"CONTENT" => array(
		"Approaches to Learning" => array(
			"Creativity",
			"Emotional Self-regulation",
			"Executive Function",
			"Initiative and Curiosity"
		),
		"Cognition" => array(
			"Logic and Reasoning",
			"Mathematical Knowledge and Skills",
			"Science Reasoning and Skills"
		),
		"Creative Arts Expression" => array(
			"Art",
			"Creative Movement and Dance",
			"Drama",
			"Music",
			"Puppets"
		),
		"Language Development" => array(
			"Attend and Understand Language",
			"Communication and Speaking",
			"Vocabulary"
		),
		"Literacy Knowledge and Skills" => array(
			"Alliteration",
			"Alphabet Knowledge",
			"Book Appreciation",
			"Comprehension and Text Structure",
			"Decoding",
			"Engagement in Literacy Activities",
			"Phonological Awareness",
			"Print Concepts and Conventions",
			"Rhyming",
			"Storybook Reading",
			"Writing Stages"
		),
		"Physical Development" => array(
			"Fine Motor",
			"Gross Motor",
			"Perception"
		),
		"Personal Care Routines" => array(
			"Greeting/Departing",
			"Health",
			"Meals",
			"Nap",
			"Self-help",
			"Toileting"
		),
		"Social and Emotional Development" => array(
			"Conflict Resolution",
			"Cooperative Play",
			"Emotional Functioning",
			"Self-regulation",
			"Sense of Identity and Belonging",
			"Social Relationships"
		),
		"Social Studies Knowledge and Skills" => array(
			"History and Events",
			"People and the Environment",
			"Self-family-community"
		)
	),
	"TEACHING PRACTICE" => array(
		"Assessment" => array(
			"Data Collection ",
			"Partner with Families",
			"Screening Tool",
			"Sharing Data"
		),
		"Classroom Organization (Preschool)" => array(
			"Behavior Management",
			"Productivity",
			"Instructional Learning Formats"
		),
		"Emotional and Behavioral Support (Toddler)" => array(
			"Behavior Guidance",
			"Emotional Literacy",
			"Positive Climate",
			"Regard for Child Perspective",
			"Teacher Sensitivity"
		),
		"Emotional Support (Preschool)" => array(
			"Emotional Literacy",
			"Positive Climate",
			"Regard for Student Perspective",
			"Teacher Sensitivite"
		),
		"Engaged Support for Learning (Toddler)" => array(
			"Facilitation of Learning and Development",
			"Quality of Feedback",
			"Language Modeling"
		),
		"Individualized Teaching and Learning" => array(
			"Consequences",
			"Culturally Responsive",
			"Curriculum Modifications",
			"Dual Language Learning Strategies",
			"Embedded Teaching",
			"Modeling",
			"Prompts",
			"Teaching Loop"
		),
		"Instructional Support (Preschool)" => array(
			"Concept Development",
			"Language Modeling ",
			"Quality of Feedback"
		),
		"Professional Development" => array(
			"Practice-Based Coaching",
			"Resilience and Wellness"
		),
		"Responsive Caregiving (Infant)" => array(
			"Early Language Support",
			"Facilitated Exploration",
			"Relational Climate",
			"Teacher Sensitivity"
		)
	)
);


// update display titles for topics and sub-topics
// search value => title to display

$search_titles = array(
);


// additional menus


$additional_menus = array(
"Setting/Age Group" => array(
	"Child Care Center",
	"Family Child Care",
	"Infant",
	"Toddler",
	"Preschool",
	"Mixed-age"
),
"Collections" => array(
	"Interviews",
	"Lectures",
	"Modules",
	"Real to Reel",
	"Shows",
	"Webinars"
)
);

?>