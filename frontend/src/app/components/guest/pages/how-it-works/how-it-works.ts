import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { LucideAngularModule, UserPlus, Plug, ArrowRightLeft, ChartLine, Coins, ShieldCheck, CheckCircle, RefreshCw, Handshake, Lightbulb, TrendingUp, ChevronDown, ChevronUp } from 'lucide-angular';

@Component({
  selector: 'app-how-it-works',
  standalone: true,
  imports: [CommonModule, LucideAngularModule],
  templateUrl: './how-it-works.html',
  styleUrl: './how-it-works.css'
})
export class HowItWorksComponent {
  readonly UserPlus = UserPlus;
  readonly Plug = Plug;
  readonly ArrowRightLeft = ArrowRightLeft;
  readonly ChartLine = ChartLine;
  readonly Coins = Coins;

  // New Icons for Features & FAQ
  readonly ShieldCheck = ShieldCheck;
  readonly CheckCircle = CheckCircle;
  readonly RefreshCw = RefreshCw;
  readonly Handshake = Handshake;
  readonly Lightbulb = Lightbulb;
  readonly TrendingUp = TrendingUp;
  readonly ChevronDown = ChevronDown;
  readonly ChevronUp = ChevronUp;

  steps = [
    {
      number: 1,
      title: 'Join the Tumirails Network',
      description: "Sign up effortlessly to become part of Ghana's decentralized energy community. Quick, secure, and user-friendly onboarding.",
      icon: UserPlus,
      connectorLabel: 'Connect'
    },
    {
      number: 2,
      title: 'Connect Your Solar System',
      description: 'Integrate your solar setup or smart meter with our platform to start monitoring and managing your energy production.',
      icon: Plug,
      connectorLabel: 'Trade'
    },
    {
      number: 3,
      title: 'Trade Energy Blocks',
      description: 'Buy or sell excess solar energy with other users on our marketplace. Fair prices, instant transactions, and full transparency.',
      icon: ArrowRightLeft,
      connectorLabel: 'Track'
    },
    {
      number: 4,
      title: 'Track Your Energy Impact',
      description: 'Monitor your energy consumption, savings, and carbon footprint in real-time. Gain insights with intuitive dashboards.',
      icon: ChartLine,
      connectorLabel: 'Save'
    },
    {
      number: 5,
      title: 'Save on Electricity Bills',
      description: 'Optimize your energy usage and transactions to significantly reduce your monthly electricity expenses and contribute to a greener future.',
      icon: Coins,
      connectorLabel: null
    }
  ];

  features = [
    {
      title: 'Robust Security Protocols',
      description: 'Your transactions and personal data are protected by state-of-the-art encryption and blockchain technology, ensuring ultimate peace of mind.',
      icon: ShieldCheck
    },
    {
      title: 'Transparent Transaction History',
      description: 'Every energy trade is recorded on an immutable ledger, providing full transparency and traceability for all participants.',
      icon: CheckCircle
    },
    {
      title: 'Effortless Energy Trading',
      description: 'Our intuitive platform makes buying and selling energy blocks simple, with real-time pricing and automated matching.',
      icon: RefreshCw
    },
    {
      title: 'Community & Collaboration',
      description: 'Connect with a growing network of prosumers and consumers, fostering a collaborative energy ecosystem in Ghana.',
      icon: Handshake
    },
    {
      title: 'Smart Energy Insights',
      description: 'Leverage AI-driven analytics to understand your energy patterns, optimize usage, and maximize your savings.',
      icon: Lightbulb
    },
    {
      title: 'Sustainable Impact',
      description: "By participating, you're actively contributing to Ghana's transition to clean energy and reducing carbon emissions.",
      icon: TrendingUp
    }
  ];

  faqs = [
    {
      question: 'What is Tumirails and how does it work?',
      answer: 'Tumirails is a decentralized energy marketplace that connects solar energy producers (prosumers) with consumers. It works by allowing prosumers to list their excess energy on the platform, which consumers can then purchase in real-time, handling all payments and tracking securely via blockchain technology.'
    },
    {
      question: 'Who can use the Tumirails platform?',
      answer: 'The platform is designed for everyone—households, businesses, and solar panel owners. Whether you want to sell your excess solar energy or buy clean, affordable electricity, Tumirails is built for you.'
    },
    {
      question: 'What kind of energy can I trade on Tumirails?',
      answer: 'Primarily solar energy. However, our platform is built to support various renewable energy sources as the grid evolves, facilitating the trade of clean, sustainable power blocks.'
    },
    {
      question: 'How secure are transactions on Tumirails?',
      answer: 'Extremely secure. We use advanced blockchain encryption to record every transaction on an immutable ledger, ensuring that all trades are transparent, tamper-proof, and safe from unauthorized access.'
    },
    {
      question: 'How do I start saving with Tumirails?',
      answer: 'Simply sign up, connect your smart meter or solar system, and start trading. By buying energy at competitive rates or selling your excess power, you can significantly lower your overall electricity costs and earn credit.'
    }
  ];

  openFaqIndex: number | null = null;

  toggleFaq(index: number) {
    this.openFaqIndex = this.openFaqIndex === index ? null : index;
  }
}
