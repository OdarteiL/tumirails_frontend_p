import { Component, OnInit, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { LucideAngularModule, DollarSign, Zap, ShieldCheck, Quote } from 'lucide-angular';

@Component({
  selector: 'app-consumers',
  standalone: true,
  imports: [CommonModule, LucideAngularModule],
  templateUrl: './consumers.html',
  styleUrl: './consumers.css'
})
export class ConsumersComponent {
  readonly DollarSign = DollarSign;
  readonly Zap = Zap;
  readonly ShieldCheck = ShieldCheck;
  readonly Quote = Quote;

  benefits = [
    {
      title: 'Affordable Energy',
      description: 'Access cost-effective solar energy blocks and reduce your monthly electricity bills significantly.',
      icon: DollarSign
    },
    {
      title: 'Smart Energy Tracking',
      description: 'Monitor your energy consumption and generation in real-time with intuitive dashboards.',
      icon: Zap
    },
    {
      title: 'Certified & Reliable Options',
      description: 'Connect with trusted, certified solar installers and vendors for high-quality equipment and services.',
      icon: ShieldCheck
    }
  ];

  steps = [
    {
      number: 1,
      title: 'Connect Your Home',
      description: 'Seamlessly integrate your home to the Tumirails network and start exploring energy options.'
    },
    {
      number: 2,
      title: 'Choose Your Energy',
      description: 'Browse the marketplace for solar blocks, panels, and services that fit your needs.'
    },
    {
      number: 3,
      title: 'Track & Save',
      description: 'Monitor your usage, optimize savings, and enjoy a consistent supply of clean energy.'
    }
  ];

  currentTestimonialIndex = 0;
  private carouselInterval: any;

  ngOnInit() {
    this.startCarousel();
  }

  ngOnDestroy() {
    this.stopCarousel();
  }

  startCarousel() {
    this.carouselInterval = setInterval(() => {
      this.currentTestimonialIndex = (this.currentTestimonialIndex + 1) % this.testimonials.length;
    }, 8000);
  }

  stopCarousel() {
    if (this.carouselInterval) {
      clearInterval(this.carouselInterval);
    }
  }

  setTestimonial(index: number) {
    this.currentTestimonialIndex = index;
    this.stopCarousel();
    this.startCarousel();
  }

  testimonials = [
    {
      quote: "Tumirails made switching to solar so simple! My bills have dropped, and I feel great knowing I'm using clean energy.",
      name: 'Aisha Mensah',
      role: 'Homeowner, Accra',
      image: 'assets/profile-aisha.png',
      isHighlighted: false
    },
    {
      quote: "The energy tracking feature is a game-changer. I can see exactly how much I'm saving and contributing to the environment.",
      name: 'Kwame Nkrumah',
      role: 'Educator, Kumasi',
      image: 'assets/profile-kwame.png', // Note: Using the generated profile
      isHighlighted: true
    },
    {
      quote: "Finding certified installers was always a worry, but Tumirails connected me with reliable experts quickly. Highly recommend!",
      name: 'Adjoa Boateng',
      role: 'Small Business Owner, Tema',
      image: 'assets/profile-adjoa.png',
      isHighlighted: false
    }
  ];
}
