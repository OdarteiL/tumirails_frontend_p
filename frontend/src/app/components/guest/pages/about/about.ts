import { Component } from '@angular/core';
import { RouterLink } from '@angular/router';
import { CommonModule } from '@angular/common';
import { Target, Eye, Lightbulb, Rocket, LucideAngularModule, type LucideIconData } from 'lucide-angular';

interface CoreValue {
  title: string;
  description: string;
  icon: LucideIconData;
}

interface JourneyMilestone {
  year: string;
  title: string;
  description: string;
}

interface TeamMember {
  name: string;
  role: string;
  image: string;
  bio: string;
}

interface Award {
  title: string;
  organization: string;
  year: string;
  image: string;
}

@Component({
  selector: 'app-about',
  standalone: true,
  imports: [RouterLink, CommonModule, LucideAngularModule],
  templateUrl: './about.html',
  styleUrl: './about.css'
})
export class AboutComponent {
  // Lucide icons
  readonly Target = Target;
  readonly Eye = Eye;

  // Core Values Data
  readonly coreValues: CoreValue[] = [
    {
      title: 'Innovation',
      description: 'Pioneering new solutions for energy independence and efficiency in Ghana.',
      icon: Lightbulb
    },
    {
      title: 'Transparency',
      description: 'Building trust through clear, honest, and open energy transactions.',
      icon: Eye
    },
    {
      title: 'Sustainability',
      description: 'Empowering a greener future with accessible and reliable solar energy.',
      icon: Target
    },
    {
      title: 'Empowerment',
      description: 'Giving communities control over their energy resources and future.',
      icon: Rocket
    }
  ];

  // Journey Milestones Data
  readonly journeyMilestones: JourneyMilestone[] = [
    {
      year: '2020',
      title: 'Founding of Tumirails',
      description: 'The journey began with a vision to revolutionize Ghana\'s energy sector.'
    },
    {
      year: '2021',
      title: 'Pilot Program Launch',
      description: 'Successful deployment of our first digital solar rail infrastructure in selected communities.'
    },
    {
      year: '2022',
      title: 'Platform Public Beta',
      description: 'Introduced the peer-to-peer energy trading marketplace to early adopters.'
    },
    {
      year: '2023',
      title: 'Nationwide Expansion',
      description: 'Expanded operations and partnerships across key regions in Ghana.'
    },
    {
      year: '2024',
      title: 'Strategic Partnerships',
      description: 'Collaborated with leading solar installers and financial institutions.'
    }
  ];

  // Team Members Data
  readonly teamMembers: TeamMember[] = [
    {
      name: 'Kwame Mensah',
      role: 'Chief Executive Officer',
      image: '/assets/placeholder-image.svg',
      bio: 'Passionate about renewable energy with 15+ years leading tech innovations in Africa.'
    },
    {
      name: 'Ama Osei',
      role: 'Chief Technology Officer',
      image: '/assets/placeholder-image.svg',
      bio: 'Expert in distributed systems and blockchain technology driving our platform architecture.'
    },
    {
      name: 'Kofi Adjei',
      role: 'Chief Operations Officer',
      image: '/assets/placeholder-image.svg',
      bio: 'Specializes in scaling operations and ensuring seamless service delivery across regions.'
    },
    {
      name: 'Akosua Boateng',
      role: 'Head of Marketing',
      image: '/assets/placeholder-image.svg',
      bio: 'Creative strategist building Tumi\'s brand and connecting with communities nationwide.'
    }
  ];

  // Awards Data
  readonly awards: Award[] = [
    {
      title: 'Best Green Energy Innovation',
      organization: 'Ghana Energy Awards',
      year: '2023',
      image: '/assets/placeholder-image.svg'
    },
    {
      title: 'Tech Startup of the Year',
      organization: 'West Africa Tech Summit',
      year: '2023',
      image: '/assets/placeholder-image.svg'
    },
    {
      title: 'Sustainable Impact Award',
      organization: 'African Innovation Foundation',
      year: '2022',
      image: '/assets/placeholder-image.svg'
    },
    {
      title: 'Digital Transformation Leader',
      organization: 'Ghana Business Excellence',
      year: '2022',
      image: '/assets/placeholder-image.svg'
    }
  ];
}
