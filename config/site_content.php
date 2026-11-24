<?php

return [
    'services' => [
        'moving-services' => [
            'image' => 'images/service-moving.webp',
            'icon' => 'images/service-moving.svg',
            'benefits' => ['House and office relocations', 'Fragile-item packing and furniture protection', 'Loading, transport and offloading', 'Unpacking and home setup'],
            'scope' => [
                [
                    'title' => 'Packing and protection',
                    'items' => [
                        'Professional packing of fragile items using plastic boxes and protective wrappers',
                        'Furniture protection with quality shrink film',
                        'Safe removal and installation of TVs and chandeliers',
                    ],
                ],
                [
                    'title' => 'Moving day',
                    'items' => [
                        'Loading, transportation and offloading of your belongings',
                        'Careful handling of household items throughout the move',
                    ],
                ],
                [
                    'title' => 'Unpacking and settling in',
                    'items' => [
                        'Light cleaning of your new home before arrangement',
                        'Unpacking and proper arrangement of household items',
                        'Kitchen setup and appliance connection',
                        'Folding and arranging clothes neatly in wardrobes',
                        'Proper setup and arrangement of seats and furniture',
                    ],
                ],
            ],
            'content' => <<<'MARKDOWN'
## House and office relocations

Our team coordinates your move from packing and protection through transport, offloading and settling into your new space. We handle household and office belongings with care and agree the work with you before moving day.

## A smoother moving day

Tell us your collection and destination addresses, the property size, access details and preferred date. For apartments or offices, include floor numbers, lift access, parking, loading restrictions and any building rules. Point out fragile items, large furniture, TVs, chandeliers and anything that needs special attention so we can plan ahead.

## Settling into your new home

After offloading, we help unpack and arrange household items, set up furniture and seats, organise clothes in wardrobes, and prepare the kitchen. Light cleaning of the new home is included before we begin arranging your belongings. Share any room-by-room priorities when requesting your booking.
MARKDOWN,
            'faqs' => [
                ['question' => 'How do I choose a moving package?', 'answer' => 'Start with your home size. Share extra items, difficult access or multiple stops so the team can confirm a suitable quotation.'],
                ['question' => 'Can I request packing help?', 'answer' => 'Yes. Include your packing needs when you request the move so the team can confirm what can be included.'],
            ],
        ],
        'fumigation-services' => [
            'image' => 'images/service-fumigation.webp',
            'icon' => 'images/service-fumigation.svg',
            'benefits' => ['Talk through the pest problem', 'Preparation guidance for your property', 'Treatment and follow-up advice'],
            'content' => <<<'MARKDOWN'
## Tell us what you have noticed

Describe the pest problem, the areas affected and any previous treatment. Mention pets, children, food preparation areas and any other details the team should account for. The team can discuss suitable pest-control options once it understands the situation.

## Prepare for the appointment

Ask for preparation and re-entry instructions before the visit. They can differ by treatment and property. Follow the directions provided for food, pets, ventilation and access to treated areas. Let the team know if you cannot follow any instruction before work starts.
MARKDOWN,
            'faqs' => [
                ['question' => 'Can I stay in the property during treatment?', 'answer' => 'Ask the team before the appointment. Whether you need to leave and when you can return depends on the treatment and the property.'],
                ['question' => 'Will one visit be enough?', 'answer' => 'That depends on the pest problem. The team can recommend next steps after reviewing the affected areas.'],
            ],
        ],
        'sofa-mattress-cleaning' => [
            'image' => 'images/service-sofa-cleaning.webp',
            'icon' => 'images/service-sofa-mattress.svg',
            'benefits' => ['Sofas and upholstered seating', 'Mattresses in common sizes', 'Care suited to the material'],
            'content' => <<<'MARKDOWN'
## Sofa and upholstery cleaning

Share the number of seats, fabric type and any stains or damage you want us to check. Care instructions and the condition of the fabric help the team determine which cleaning method is suitable.

## Mattress cleaning

Tell us the mattress size and any areas that need particular attention. Ask the team about drying and when to put bedding back on. Materials, ventilation and site conditions affect drying time.
MARKDOWN,
            'faqs' => [
                ['question' => 'Will every stain come out?', 'answer' => 'Results depend on the fabric, the stain and how long it has been there. Tell us about the mark so the team can set expectations.'],
                ['question' => 'How should I prepare the items?', 'answer' => 'Remove bedding and loose items, clear access, and share any manufacturer cleaning instructions you have.'],
            ],
        ],
        'house-deep-cleaning' => [
            'image' => 'images/service-house-cleaning.webp',
            'icon' => 'images/service-deep-cleaning.svg',
            'benefits' => ['Kitchens and bathrooms', 'Bedrooms and living areas', 'Scope agreed before the visit'],
            'content' => <<<'MARKDOWN'
## Kitchens and bathrooms

Let us know which surfaces and fixtures need attention and whether there are stubborn marks or areas with heavy buildup. We will discuss the work needed and any surfaces that require special care.

## Living areas and bedrooms

Share your property size and priorities. If you need specific work such as inside appliances, cupboards or windows, include it in your request so the team can confirm whether it is available and how it affects the quotation.

## Before the team arrives

Arrange access to the property. Put away valuables and tell the team about restricted rooms or any areas that need special care.
MARKDOWN,
            'faqs' => [
                ['question' => 'Can I request cleaning before moving in?', 'answer' => 'Yes. Tell us when the property will be accessible and which areas need attention.'],
                ['question' => 'Are appliances and windows included?', 'answer' => 'Describe any appliance or window cleaning you need. The team will confirm what is included in your agreed scope.'],
            ],
        ],
    ],
    'project_photos' => [
        ['path' => 'images/projects/team-packing-sofa.jpeg', 'caption' => 'The team carefully wraps and packs a sofa for a move.', 'alt_text' => 'Two Home Movers and Cleaners team members wrapping a sofa in a living room.', 'category' => 'Moving'],
        ['path' => 'images/projects/team-preparing-bed.jpeg', 'caption' => 'Preparing a bed frame for safe transport.', 'alt_text' => 'The moving team carefully disassembling a bed frame in a bedroom.', 'category' => 'Moving'],
        ['path' => 'images/projects/team-carrying-wrapped-chair.jpeg', 'caption' => 'A protected armchair carried carefully through a doorway.', 'alt_text' => 'A mover carrying a wrapped armchair through a doorway.', 'category' => 'Moving'],
        ['path' => 'images/projects/team-moving-apartment.jpeg', 'caption' => 'Our crew coordinating a move at an apartment building.', 'alt_text' => 'A Home Movers and Cleaners team member coordinating a move at an apartment building.', 'category' => 'Moving'],
        ['path' => 'images/projects/team-loading-truck.jpeg', 'caption' => 'Loading furniture and household belongings for transport.', 'alt_text' => 'A Home Movers and Cleaners team member loading belongings into a moving truck.', 'category' => 'Moving'],
        ['path' => 'images/projects/move-in-bedroom-cleaning.jpeg', 'caption' => 'A bedroom floor cleaned as part of move-in preparation.', 'alt_text' => 'A Home Movers and Cleaners team member mopping a bedroom floor.', 'category' => 'House Deep Cleaning'],
        ['path' => 'images/projects/team-arranging-wardrobe.jpeg', 'caption' => 'Helping arrange clothes neatly in a wardrobe after a move.', 'alt_text' => 'A Home Movers and Cleaners team member arranging folded clothes in a wardrobe.', 'category' => 'Moving'],
    ],
    'pages' => [
        'about' => [
            'title' => 'About Home Movers and Cleaners',
            'summary' => 'Moving and cleaning services for homes and businesses in Nairobi.',
            'body' => "## What we do\n\nWe bring household moving, fumigation, upholstery care and deep cleaning together in one place. You can ask for one service or discuss what makes sense for a move, a new home or a property that needs attention.\n\n## How we work\n\nTell us about the property, your preferred timing and the work that matters most. We review the details and confirm the scope, availability and price with you before the visit.\n\n## Service area\n\nWe serve Nairobi. Share your location or the addresses for your move so the team can check availability.",
        ],
        'privacy' => [
            'title' => 'Privacy Policy',
            'summary' => 'How we handle information you share through this website.',
            'body' => "## Information we collect\n\nWhen you enquire or request a booking, we receive the details you provide, such as your contact information, service addresses, preferred dates and notes. Website sessions also support form security and admin sign-in.\n\n## Why we use it\n\nWe use these details to respond, evaluate availability, arrange requested work and manage related communications. We may use hosting or email providers to operate the site and deliver messages. If you choose to contact us on WhatsApp, that service handles the information you share through it.\n\n## Retention and your rights\n\nInformation should be kept only as long as needed for service administration and applicable obligations. You can ask how your information is used, request access or corrections, or exercise other applicable data rights through our contact page. You can also contact Kenya’s Office of the Data Protection Commissioner.",
        ],
        'terms' => [
            'title' => 'Terms of Service',
            'summary' => 'Information about service requests, quotations and customer responsibilities.',
            'body' => "## Requests and confirmation\n\nAn online request is pending until our team confirms it. The final service scope, timing and price depend on the details agreed with the team.\n\n## Quotations and payments\n\nStarting prices are guides. Ask us to confirm what is included and any additional charges or payment arrangements before accepting a quotation.\n\n## Access and preparation\n\nProvide accurate addresses, access details and instructions. Tell us in advance about restricted access, fragile items or extra work that is outside the agreed scope. Requirements and limitations should be discussed when confirming the service.",
        ],
        'cookies' => [
            'title' => 'Cookie Policy',
            'summary' => 'How website cookies support your visit.',
            'body' => "## Essential cookies\n\nThe website uses session and security cookies to support form submissions and admin sign-in. Blocking them may affect those functions.\n\n## External services\n\nLinks to services such as WhatsApp or social networks take you to websites with their own cookie practices. You can manage cookies in your browser settings.",
        ],
        'booking-cancellation' => [
            'title' => 'Booking and Cancellation Policy',
            'summary' => 'How to request a service or ask to change a booking.',
            'body' => "## Scheduling a service\n\nUse the booking form to request a time. Our team reviews the request and confirms the service details separately.\n\n## Changing a booking\n\nIf you need to reschedule or cancel, contact us with your booking reference as soon as you can. Please check any cancellation or rescheduling terms that were provided when your service was confirmed.",
        ],
        'refunds' => [
            'title' => 'Refund Policy',
            'summary' => 'How to raise a question about a payment or refund.',
            'body' => "## Request a review\n\nIf you have a refund question, contact us with your booking reference and any relevant payment details. Do not send payment card credentials.\n\n## What we consider\n\nThe team reviews the circumstances, the terms agreed for the service and applicable consumer rights. Ask us about the payment and refund terms before accepting your quotation.",
        ],
    ],
];
